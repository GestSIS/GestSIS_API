<?php

namespace App\Domaine\Business;

use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\HeureExercice;
use App\Infrastructure\Models\HeureExerciceType;
use Ds\Set;
use Illuminate\Database\Eloquent\Collection;

class ExerciceBusiness
{
    // Statut:
    // 0 -> Annulé
    // 1 -> A saisir
    // 2 -> En attente de validation
    // 3 -> Disponible pour imputation
    // 4 -> Imputée
    public const EXERCICE_STATUT_ANNULE = 0;
    public const EXERCICE_STATUT_EMPTY = 1;
    public const EXERCICE_STATUT_SAISI = 2;
    public const EXERCICE_STATUT_VALIDE = 3;
    public const EXERCICE_STATUT_IMPUTE = 4;

    protected $repository;

    public function __construct(ExerciceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Modifie le statut à saisi si toutes les présences ont été saisies
     */
    private function updateStatut($exerciceId)
    {
        $statut = $this->repository->getExerciceStatutById($exerciceId);
        if ($statut == self::EXERCICE_STATUT_ANNULE || $statut > self::EXERCICE_STATUT_VALIDE) {
            return $statut;
        }

        // Check saisi des présences sont saisies
        $presences = $this->repository->listeSapeurOfExerciceById($exerciceId);
        $presenceIncompletes = array_filter($presences, function ($p) {
            // Si convoqué alors une saisie doit être faite pour chaque sapeur
            return $p->convoque && !$p->present && !$p->amende && !$p->remplace && !$p->excuse_type_id;
        });

        // Update statut si l'exercice est incomplet
        if (count($presenceIncompletes) > 0) {
            $statut = self::EXERCICE_STATUT_EMPTY;
        } else {
            $statut = max($statut, self::EXERCICE_STATUT_SAISI);
        }
        $this->repository->updateExerciceById($exerciceId, array("statut" => $statut));

        return $statut;
    }

    /**
     * Create a exercice
     *
     * @param $data
     * @return ExerciceBusiness
     * @throws ArrayException
     */
    public function createExercice($data)
    {
        // Statut:
        // 0 -> annulé
        // 1 -> vide
        // 2 -> saisie
        // 3 -> validé
        // 4 -> imputé
        $data['statut'] = self::EXERCICE_STATUT_EMPTY;
        return $this->repository->createExercice($data);
    }

    public function cancelExerciceById($exerciceId)
    {
        $statut = $this->repository->getExerciceStatutById($exerciceId);
        if ($statut == self::EXERCICE_STATUT_ANNULE || $statut > self::EXERCICE_STATUT_VALIDE) {
            return $statut;
        }

        $this->repository->updateExerciceById($exerciceId, array("statut" => self::EXERCICE_STATUT_ANNULE));
        return self::EXERCICE_STATUT_ANNULE;
    }

    public function reactivateExerciceById($exerciceId)
    {
        $statut = $this->repository->getExerciceStatutById($exerciceId);
        if ($statut != self::EXERCICE_STATUT_ANNULE) {
            return $statut;
        }

        $this->repository->updateExerciceById($exerciceId, array("statut" => self::EXERCICE_STATUT_EMPTY));
        $statut = $this->updateStatut($exerciceId);
        return $statut;
    }

    public function deleteExerciceById($exerciceId)
    {
        // Check pas déjà imputé
        $statut = $this->repository->getExerciceStatutById($exerciceId);
        if ($statut > self::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException([], "Impossible de supprimer un exercice déjà imputé");
        }

        $this->repository->deleteExerciceById($exerciceId);
    }

    public function validateExerciceById($exerciceId)
    {
        $statut = $this->repository->getExerciceStatutById($exerciceId);
        if ($statut !== self::EXERCICE_STATUT_SAISI) {
            throw new ArrayException(["message" => "Impossible de valider l'exercice."]);
        }

        // Check saisi des présences sont saisies
        $presences = $this->repository->listeSapeurOfExerciceById($exerciceId);
        $presenceIncompletes = array_filter($presences, function ($p) {
            // Si convoqué alors une saisie doit être faite pour chaque sapeur
            return $p->convoque && !$p->present && !$p->amende && !$p->remplace && !$p->excuse_type_id;
        });
        if (count($presenceIncompletes)) {
            throw new ArrayException(["message" => "Certains sapeurs convoqué sont incomplet"]);
        }

        return $this->repository->updateExerciceById($exerciceId, [
            "statut" => self::EXERCICE_STATUT_VALIDE
        ]);
    }

    /**
     * Update presences à partir d'une liste complète saisie
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public function updatePresences($exerciceId, $presences)
    {
        // Fetch exercice
        $exercice = Exercice::with("sapeurs")->where('id', '=', $exerciceId)->first();

        // Ignore si l'exercice n'existe plus
        if ($exercice == NULL) {
            return;
        }

        // Ignore si déjà imputé
        if ($exercice->statut === self::EXERCICE_STATUT_IMPUTE) {
            return;
        }

        // Ajout des sapeurs manquants
        $sapeursIdsActuel = new Set(array_map(function ($e) {
            return $e['sapeur_id'];
        }, $exercice->sapeurs->toArray()));

        // Sapeurs non présent mais avec des heures pas pris en compte ?
        $sapeursAjoutes = array_filter($presences, fn ($e) => !$sapeursIdsActuel->contains($e['sapeur_id']));
        $this->addSapeurs($exerciceId, $sapeursAjoutes);

        // Updated sapeurs
        $sapeursModifies = array_filter($presences, fn ($e) => $sapeursIdsActuel->contains($e['sapeur_id']));
        $this->updateSapeurs($exerciceId, $sapeursModifies, false);

        // On ignore les sapeurs déjà saisi mais non présent dans les présences envoyées

        // Modification du status
        $this->updateStatut($exerciceId);
    }

    /**
     * Ajout de sapeurs d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public function addSapeurs($exerciceId, $sapeurs)
    {
        // Check pas déjà imputé
        $statut = $this->repository->getExerciceStatutById($exerciceId);
        if ($statut == self::EXERCICE_STATUT_ANNULE || $statut > self::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException([], 'Impossible de modifier un exercice déjà imputé');
        }

        // Check sapeur not duplicated
        $ids = array_map(function ($sap) {
            return $sap->sapeur_id;
        }, $this->repository->listeSapeurOfExerciceById($exerciceId));

        $sapeurFiltered = array_filter($sapeurs, function ($sap) use ($ids) {
            return !in_array($sap['sapeur_id'], $ids);
        });

        foreach ($sapeurFiltered as $sapeur) {
            $this->repository->addSapeurToExercice($exerciceId, $sapeur);

            // Ajout heures sup if any
            $heures = array_filter(
                array_key_exists('heures', $sapeur) ? $sapeur['heures'] : [],
                fn ($h) => array_key_exists('quantite', $h) && !is_null($h['quantite']) && $h['quantite'] > 0
            );
            foreach ($heures as $heure) {
                if (!HeureExerciceType::where('id', '=', $heure['heure_exercice_type_id'])->exists()) {
                    // On ignore le type d'heure n'existant plus
                    throw new ArrayException(["Message" => "Unknown heure type"]);
                    continue;
                }
                $heure['sapeur_id'] = $sapeur['sapeur_id'];
                $this->ajouterHeureExercice($exerciceId, $heure);
            }
        }

        return $this->updateStatut($exerciceId);
    }

    /**
     * Modification de sapeurs d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public function updateSapeurs($exerciceId, $sapeurs, $hasValidatePermission)
    {
        // Check pas déjà imputé
        $statut = $this->repository->getExerciceStatutById($exerciceId);

        if (!$hasValidatePermission && $statut >= self::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException([], 'Permissions insuffisantes pour modifier les présences.');
        }

        $cachedHeures = HeureExercice
            ::where('exercice_id', $exerciceId)
            ->get()->toArray();
        $indexedHeures = [];
        foreach ($cachedHeures as $heure) {
            $indexedHeures[$heure['id']] = $heure;
        }

        // Check si pas déjà imputé
        if ($statut >= self::EXERCICE_STATUT_IMPUTE) {
            // check que les modifications n'ont lieu que sur l'excuse type, remplacé ou amende
            $heures = HeureExercice
                ::where('exercice_id', $exerciceId)
                ->get()->toArray();
            $exerciceSapeurs = ExerciceSapeur
                ::where('exercice_id', $exerciceId)
                ->get()->toArray();

            $dictionary = [];
            foreach ($exerciceSapeurs as $sapeur) {
                $dictionary[$sapeur['sapeur_id']] = $sapeur;
                $dictionary[$sapeur['sapeur_id']]['heures'] = [];
            }
            foreach ($heures as $heure) {
                if (!array_key_exists($heure['sapeur_id'], $dictionary)) {
                    $dictionary[$heure['sapeur_id']] = [
                        'convoque' => False,
                        'present' => False,
                        'amende' => False,
                        'remplace' => False,
                        'excuse_type_id' => null,
                        'heures' => [],
                    ];
                }
                $dictionary[$heure['sapeur_id']]['heures'][] = $heure;
            }

            $presencesEffectives = $dictionary;

            // Check si d'autres modifications sont proposées
            foreach ($sapeurs as $sapeur) {
                $presenceEffective = $presencesEffectives[$sapeur['sapeur_id']];
                if ($presenceEffective['present'] != $sapeur['present']) {
                    throw new ArrayException([], 'Permissions insuffisantes pour modifier les présences.');
                }

                $heures = array_filter(
                    array_key_exists('heures', $sapeur) ? $sapeur['heures'] : [],
                    fn ($h) => array_key_exists('quantite', $h) && !is_null($h['quantite']) && $h['quantite'] > 0
                );
                $heuresId = array_filter(array_map(fn ($h) => array_key_exists('id', $h) ? $h['id'] : null, $heures), fn ($h) => !is_null($h));

                // Heures supprimées
                $heuresSupprimeesId = array_map(fn ($h) => $h['id'], array_filter($cachedHeures, fn ($h) => $h['sapeur_id'] == $sapeur['sapeur_id'] && !in_array($h['id'], $heuresId)));
                if (count($heuresSupprimeesId) > 0) {
                    throw new ArrayException([], 'Permissions insuffisantes pour modifier les présences.');
                }

                // Heures ajoutées
                $heuresAjoutees = array_filter($heures, fn ($heure) => !isset($heure['id']) || !$heure['id']);
                foreach ($heuresAjoutees as $heure) {
                    if (!array_key_exists('heure_exercice_type_id', $heure)) {
                        // On ignore l'heure invalide
                        continue;
                    }
                    throw new ArrayException([], 'Permissions insuffisantes pour modifier les présences.');
                }

                // Heures modifiées
                $heuresModifiees = array_filter($heures, fn ($heure) => isset($heure['id']) && $heure['id'] && !in_array($heure['id'], $heuresSupprimeesId));
                foreach ($heuresModifiees as $heure) {
                    $heureActuelle = $indexedHeures[$heure['id']];

                    // Check qu'aucune heure n'a été modifiée
                    if ($heureActuelle['quantite'] != $heure['quantite']) {
                        throw new ArrayException([], 'Permissions insuffisantes pour modifier les présences.');
                    }
                }
            }
        }

        foreach ($sapeurs as $sapeur) {
            ExerciceSapeur
                ::where('exercice_id', $exerciceId)
                ->where('id', $sapeur['id'])
                ->update([
                    'convoque' => $sapeur['convoque'],
                    'present' => $sapeur['present'],
                    'amende' => $sapeur['amende'],
                    'remplace' => $sapeur['remplace'],
                    'excuse_type_id' => $sapeur['excuse_type_id'],
                ]);

            $heures = array_filter(
                array_key_exists('heures', $sapeur) ? $sapeur['heures'] : [],
                fn ($h) => array_key_exists('quantite', $h) && !is_null($h['quantite']) && $h['quantite'] > 0
            );
            $heuresId = array_filter(array_map(fn ($h) => array_key_exists('id', $h) ? $h['id'] : null, $heures), fn ($h) => !is_null($h));

            // Heures supprimées
            $heuresSupprimeesId = array_map(fn ($h) => $h['id'], array_filter($cachedHeures, fn ($h) => $h['sapeur_id'] == $sapeur['sapeur_id'] && !in_array($h['id'], $heuresId)));
            HeureExercice::where('exercice_id', $exerciceId)
                ->where('sapeur_id', $sapeur['sapeur_id'])
                ->whereIn('id', $heuresSupprimeesId)
                ->delete();

            // Heures ajoutées
            $heuresAjoutees = array_filter($heures, fn ($heure) => !isset($heure['id']) || !$heure['id']);
            foreach ($heuresAjoutees as $heure) {
                if (!array_key_exists('heure_exercice_type_id', $heure)) {
                    // On ignore l'heure invalide
                    continue;
                }
                $heure['sapeur_id'] = $sapeur['sapeur_id'];
                $this->ajouterHeureExercice($exerciceId, $heure);
            }

            // Heures modifiées
            $heuresModifiees = array_filter($heures, fn ($heure) => isset($heure['id']) && $heure['id'] && !in_array($heure['id'], $heuresSupprimeesId));
            foreach ($heuresModifiees as $heure) {
                HeureExercice::where('exercice_id', $exerciceId)
                    ->where('sapeur_id', $sapeur['sapeur_id'])
                    ->where('id', $heure['id'])
                    ->update(['quantite' => $heure['quantite']]);
            }
            // throw new ArrayException(['ajoutes' => $heuresAjoutees, 'modifies' => $heuresModifiees, 'supprimes' => $heuresSupprimeesId]);
        }

        return $this->updateStatut($exerciceId);
    }

    /**
     * Suppression de sapeurs d'un exercice
     *
     * @param $data
     */
    public function removeSapeurs($exerciceId, $ids)
    {
        // Check pas déjà imputé
        $statut = $this->repository->getExerciceStatutById($exerciceId);
        if ($statut == self::EXERCICE_STATUT_ANNULE || $statut > self::EXERCICE_STATUT_SAISI) {
            throw new ArrayException([], 'Impossible de modifier un exercice déjà imputé');
        }

        $this->repository->removeSapeursFromExercice($exerciceId, $ids);
        HeureExercice::where('exercice_id', $exerciceId)
            ->whereIn('sapeur_id', $ids)
            ->delete();

        return $this->updateStatut($exerciceId);
    }

    public function supprimerConvocations($sapeurId, $exerciceSapeursIds)
    {
        // TODO: Check pour chaque exercice s'il est possible de supprimer la convocation
        // et donc que l'exercice n'est pas déjà imputé
        $this->repository->supprimerConvocations($sapeurId, $exerciceSapeursIds);
        HeureExercice::whereIn('id', $exerciceSapeursIds)
            ->where('sapeur_id', $sapeurId)
            ->delete();
        return true;
    }

    public function ajouterHeureExercice($exerciceId, $data)
    {
        $type = HeureExerciceType::find($data['heure_exercice_type_id']);

        $heure = new HeureExercice();
        $heure->fill($type->toArray());
        $heure->fill($data);
        $heure->exercice_id = $exerciceId;
        $heure->save();
        return $heure;
    }

    public function modifierHeureExercice($exerciceId, $id, $data)
    {
        HeureExercice::where([['id', $id], ['exercice_id', $exerciceId]])->limit(1)->update($data);
        return HeureExercice::find($id);
    }

    public function supprimerHeureExercice($exerciceId, $id)
    {
        HeureExercice::where([['id', $id], ['exercice_id', $exerciceId]])->limit(1)->delete();
    }
}
