<?php

namespace App\Domaine\Business;

use App\Application\Typst\TypstTemplate;
use App\Application\Typst\TypstToPdfGenerator;
use App\Domaine\Exceptions\InvalidActionException;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Civilite;
use App\Models\ConvocationParam;
use App\Models\ExcuseParam;
use App\Models\ExcuseType;
use App\Models\Exercice;
use App\Models\ExerciceCategorie;
use App\Models\ExerciceSapeur;
use App\Models\Fonction;
use App\Models\HeureExercice;
use App\Models\HeureExerciceType;
use App\Models\Localite;
use App\Models\Sapeur;
use App\Models\Sms;
use Carbon\Carbon;
use Ds\Set;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExerciceBusiness
{
    // Statut:
    // 0 -> Annulé
    // 1 -> A saisir
    // 2 -> En attente de validation
    // 3 -> Disponible pour imputation
    // 4 -> Imputée
    public const EXERCICE_STATUT_ANNULE = 0;
    public const EXERCICE_STATUT_VIDE = 1;
    public const EXERCICE_STATUT_SAISI = 2;
    public const EXERCICE_STATUT_VALIDE = 3;
    public const EXERCICE_STATUT_IMPUTE = 4;

    public const EXCUSE_STATUT_AMENDEE = -2;
    public const EXCUSE_STATUT_REFUSEE = -1;
    public const EXCUSE_STATUT_A_TRAITER = 0;
    public const EXCUSE_STATUT_ACCEPTEE = 1;

    /**
     * Modifie le statut de l'exercice saisi si toutes les présences ont été saisies
     */
    public static function updateStatut($exerciceId)
    {
        $statut = Exercice::findOrFail($exerciceId)->statut;
        if ($statut == self::EXERCICE_STATUT_ANNULE || $statut > self::EXERCICE_STATUT_VALIDE) {
            return $statut;
        }

        // Check saisi des présences sont saisies
        $presences = ExerciceSapeur::where('exercice_id', $exerciceId)->get()->toArray();
        $presenceIncompletes = array_filter($presences, function ($p) {
            // Si convoqué alors une saisie doit être faite pour chaque sapeur
            return ($p['convoque'] ?? false) && !($p['present'] ?? false) && !($p['absent'] ?? false) && !($p['amende'] ?? false) && !($p['remplace'] ?? false) && !($p['excuse_type_id'] ?? null);
        });

        // Update statut si l'exercice est incomplet
        if (count($presenceIncompletes) > 0) {
            $statut = self::EXERCICE_STATUT_VIDE;
        } else {
            $statut = max($statut, self::EXERCICE_STATUT_SAISI);
        }

        self::updateExerciceById($exerciceId, ["statut" => $statut]);

        return $statut;
    }

    /**
     * Create a exercice
     *
     * @param $data
     * @throws ArrayException
     */
    public static function createExercice($data): Exercice
    {
        $data['statut'] = self::EXERCICE_STATUT_VIDE;

        $data['lieu'] ??= '';
        $data['communications'] ??= '';

        $exercice = new Exercice();
        $exercice->fill($data);
        $exercice->exercice_categorie_id = $data['exercice_categorie_id'];
        $exercice->exercice_comptable_id = $data['exercice_comptable_id'];
        $exercice->save();

        return $exercice;
    }

    private static function updateExerciceById($exerciceId, $data)
    {
        $data['lieu'] ??= '';
        $data['communications'] ??= '';

        $exercice = Exercice::findOrFail($exerciceId);
        $exercice->update($data);

        return $exercice;
    }

    public static function updatExercice($exerciceId, $data)
    {
        return self::updateExerciceById($exerciceId, $data);
    }

    public static function cancelExerciceById($exerciceId)
    {
        $statut = Exercice::findOrFail($exerciceId)->statut;
        if ($statut == self::EXERCICE_STATUT_ANNULE || $statut > self::EXERCICE_STATUT_VALIDE) {
            return $statut;
        }

        self::updateExerciceById($exerciceId, ["statut" => self::EXERCICE_STATUT_ANNULE]);
        return self::EXERCICE_STATUT_ANNULE;
    }

    public static function reactivateExerciceById($exerciceId)
    {
        $statut = Exercice::findOrFail($exerciceId)->statut;
        if ($statut != self::EXERCICE_STATUT_ANNULE) {
            return $statut;
        }

        self::updateExerciceById($exerciceId, ["statut" => self::EXERCICE_STATUT_VIDE]);
        $statut = self::updateStatut($exerciceId);
        return $statut;
    }

    public static function deleteExerciceById($exerciceId)
    {
        // Check pas déjà imputé
        $statut = Exercice::findOrFail($exerciceId)->statut;
        if ($statut > self::EXERCICE_STATUT_VALIDE) {
            throw new InvalidActionException([], "Impossible de supprimer un exercice déjà imputé");
        }

        ExerciceSapeur::where('exercice_id', '=', $exerciceId)->delete();
        HeureExercice::where('exercice_id', '=', $exerciceId)->delete();
        Sms::where('exercice_id', '=', $exerciceId)->delete();
        Exercice::whereId($exerciceId)->delete();
    }

    public static function validateExerciceById($exerciceId)
    {
        $statut = Exercice::findOrFail($exerciceId)->statut;
        if ($statut !== self::EXERCICE_STATUT_SAISI) {
            throw new ArrayException(["message" => "Impossible de valider l'exercice."]);
        }

        // Check saisi des présences sont saisies
        $presences = ExerciceSapeur::where('exercice_id', $exerciceId)->get()->toArray();
        $presenceIncompletes = array_filter($presences, function ($p) {
            // Si convoqué alors une saisie doit être faite pour chaque sapeur
            return ($p['convoque'] ?? false)
                && !($p['present'] ?? false)
                && !($p['absent'] ?? false)
                && !($p['amende'] ?? false)
                && !($p['remplace'] ?? false)
                && !($p['excuse_type_id'] ?? null);
        });
        if (count($presenceIncompletes)) {
            throw new ArrayException(["message" => "Certains sapeurs convoqué sont incomplet"]);
        }

        // TODO: Valider les absences amendées ?? Non

        return self::updateExerciceById($exerciceId, [
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
    public static function updateSapeurPresences($presences, $hasValidationPermission)
    {
        $exerciceIds = array_map(fn($e) => $e['exercice_id'], $presences);

        // fetch exercices status
        $exercices = Exercice::whereIn('id', $exerciceIds)->get(['id', 'statut'])->toArray();
        $indexedExercice = [];
        foreach ($exercices as $exercice) {
            $indexedExercice[$exercice['id']] = $exercice['statut'];
        }

        foreach ($presences as $presence) {
            if ($presence['excuse_type_id'] == 0) {
                $presence['excuse_type_id'] = null;
            }

            // Check si statut de l'exercice
            $exerciceStatut = $indexedExercice[$presence['exercice_id']] ?? null;
            if ($exerciceStatut == null) {
                continue;
            }
            // Check si imputé
            if ($exerciceStatut === self::EXERCICE_STATUT_IMPUTE && $hasValidationPermission) {
                // Update uniquement de l'excuse type et amende
                $p = ExerciceSapeur::where([
                    ['id', '=', $presence['id']],
                    ['exercice_id', '=', $presence['exercice_id']],
                    ['present', '=', 0],
                ])->first();

                // Controller que le statut de present est compatible
                if ($p != null) {
                    ExerciceSapeur::where([
                        ['id', '=', $presence['id']],
                        ['sapeur_id', '=', $presence['sapeur_id']],
                        ['exercice_id', '=', $presence['exercice_id']],
                    ])->update([
                                'convoque' => $presence['convoque'],
                                'remplace' => $presence['remplace'],
                                'excuse_type_id' => $presence['excuse_type_id'],
                            ]);
                } else {
                    ExerciceSapeur::where([
                        ['id', '=', $presence['id']],
                        ['sapeur_id', '=', $presence['sapeur_id']],
                        ['exercice_id', '=', $presence['exercice_id']],
                    ])->update([
                                'convoque' => $presence['convoque'],
                            ]);
                }
            } else if ($exerciceStatut === self::EXERCICE_STATUT_ANNULE) {
                continue;
            } else if ($exerciceStatut === self::EXERCICE_STATUT_VALIDE && $hasValidationPermission || $exerciceStatut < self::EXERCICE_STATUT_VALIDE) {
                // Update all
                ExerciceSapeur::where([
                    ['id', '=', $presence['id']],
                    ['sapeur_id', '=', $presence['sapeur_id']],
                    ['exercice_id', '=', $presence['exercice_id']],
                ])->update([
                            'convoque' => $presence['convoque'],
                            'present' => $presence['present'],
                            'absent' => $presence['absent'],
                            'remplace' => $presence['remplace'],
                            'excuse_type_id' => $presence['excuse_type_id'],
                        ]);
            }
        }
    }

    public static function creerExcuse($sapeurId, $exerciceId, $excuse, $file, $sisKey)
    {
        $param = ExcuseParam::first();

        // Check que le module est activé
        $param = ExcuseParam::first();
        if (!$param || !$param->actif) {
            throw new ArrayException([], 'Module excuse non activé');
        }

        // Charger la présence
        $exerciceSapeur = ExerciceSapeur::with('exercice')
            ->where('sapeur_id', '=', $sapeurId)
            ->where('exercice_id', '=', $exerciceId)
            ->first();

        if (!$exerciceSapeur) {
            throw new ArrayException([], "Convocation introuvable");
        }

        // Check que le délai de réponse n'est pas dépassé
        $now = Carbon::now();
        $now->setTime(0, 0);

        if (Carbon::createFromFormat("Y-m-d", $exerciceSapeur->exercice->date)->addDays($param->delai_excuse)->lt($now)) {
            throw new ArrayException([], "Délai d'excuse expiré, $param->delai_excuse jours");
        }

        // Vérifier que le sapeur est convoqué à cet exercice
        if (!$exerciceSapeur->convoque) {
            throw new ArrayException([], "Vous n'êtes pas convoqué à cet exercice");
        }

        // Vérifier que l'excuse n'a pas encore été traité
        if ($exerciceSapeur->excuse_statut != self::EXCUSE_STATUT_A_TRAITER) {
            throw new ArrayException([], "Excuse déjà traitée, impossible de la modifier");
        }

        // Check si justificatif
        if ($file) {
            if ($exerciceSapeur->justificatif_path) {
                Storage::delete($exerciceSapeur->justificatif_path);
            }
            // Then add the new one
            $exerciceSapeur['justificatif_path'] = $file->store('documents/' . $sisKey . '/excuses');
            $exerciceSapeur['justificatif_filename'] = $file->getClientOriginalName();
        }

        $exerciceSapeur['excuse_type_id'] = $excuse['excuse_type_id'];
        $exerciceSapeur['remarque'] = $excuse['remarque'] ?? '';

        // Créer excuse
        $exerciceSapeur->save();
        return $exerciceSapeur;
    }

    /**
     * Update presences à partir d'une liste complète saisie
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public static function updatePresences($exerciceId, $presences)
    {
        // Fetch exercice
        $exercice = Exercice::with("sapeurs")->whereId($exerciceId)->first();

        // Ignore si l'exercice n'existe plus
        if ($exercice == NULL) {
            return;
        }

        // Ignore si déjà imputé
        if ($exercice->statut === self::EXERCICE_STATUT_IMPUTE) {
            // FIXME: permettre le changement du type d'excuse/amende
            return;
        }

        // Ajout des sapeurs manquants
        $sapeursIdsActuel = new Set(array_map(function ($e) {
            return $e['sapeur_id'];
        }, $exercice->sapeurs->toArray()));

        // Sapeurs non présent mais avec des heures pas pris en compte ?
        $sapeursAjoutes = array_filter($presences, fn($e) => !$sapeursIdsActuel->contains($e['sapeur_id']));
        self::addSapeurs($exerciceId, $sapeursAjoutes);

        // Updated sapeurs
        $sapeursModifies = array_filter($presences, fn($e) => $sapeursIdsActuel->contains($e['sapeur_id']));
        self::updateSapeurs($exerciceId, $sapeursModifies, false);

        // On ignore les sapeurs déjà saisi mais non présent dans les présences envoyées

        // Modification du status
        self::updateStatut($exerciceId);
    }

    /**
     * Modification d'une présence d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public static function updatePresence($presenceId, $presence, $file, $hasValidationPermission, $sisKey)
    {
        $exerciceSapeur = ExerciceSapeur::with('exercice')->find($presenceId);

        // Ignore si l'exercice n'existe plus
        if ($exerciceSapeur == NULL) {
            return;
        }

        $exercice = $exerciceSapeur->exercice;
        $sapeurId = $exerciceSapeur->sapeur_id;
        $exerciceId = $exercice->id;

        // Ignore si déjà imputé
        if (!$hasValidationPermission && $exercice->statut >= self::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException([$exercice->statut], 'Permissions insuffisantes pour modifier les présences.');
        }

        // Check si pas déjà imputé
        if ($exercice->statut >= self::EXERCICE_STATUT_IMPUTE) {
            self::checkValiditeChangementsSiImpute($presenceId, $exerciceId, $sapeurId, $presence);
        }

        // Check si justificatif
        if ($file) {
            if ($exerciceSapeur->justificatif_path) {
                Storage::delete($exerciceSapeur->justificatif_path);
            }
            // Then add the new one
            $presence['justificatif_path'] = $file->store('documents/' . $sisKey . '/excuses');
            $presence['justificatif_filename'] = $file->getClientOriginalName();
        } else {
            $presence['justificatif_path'] = $exerciceSapeur->justificatif_path;
            $presence['justificatif_filename'] = $exerciceSapeur->justificatif_filename;
        }

        // Changement de la présence
        {
            if ($presence['present'] == true || $presence['remplace'] == true) {
                $presence['excuse_statut'] = ExerciceBusiness::EXCUSE_STATUT_A_TRAITER;
            }

            // Permission: Saisie présence 
            ExerciceSapeur
                ::where('exercice_id', $exerciceId)
                ->whereId($presenceId)
                ->update([
                    'convoque' => $presence['convoque'],
                    'present' => $presence['present'],
                    'absent' => $presence['absent'],
                    'remplace' => $presence['remplace'],

                    ...($hasValidationPermission ? [
                        'excuse_type_id' => $presence['excuse_type_id'] == 0 ? null : $presence['excuse_type_id'],
                        'excuse_statut' => $presence['excuse_statut'],

                        'remarque' => $presence['remarque'] ?? '',
                        'justificatif_path' => $presence['justificatif_path'],
                        'justificatif_filename' => $presence['justificatif_filename'],
                        'justification' => $presence['justification'] ?? '',
                    ] : [])
                ]);
        }

        return self::updateStatut($exerciceId);
    }

    private static function checkValiditeChangementsSiImpute($exerciceSapeurId, $exerciceId, $sapeurId, $presenceEffective)
    {
        // check que les modifications n'ont lieu que sur l'excuse type, remplacé ou amende
        $presenceReference = ExerciceSapeur
            ::find($exerciceSapeurId);
        $heuresReferences = HeureExercice
            ::where('exercice_id', $exerciceId)
            ->where('sapeur_id', $sapeurId)
            ->get()->toArray();

        // Check si d'autres modifications sont proposées
        if ($presenceEffective['present'] != $presenceReference['present']) {
            throw new ArrayException([], 'Permissions insuffisantes pour modifier les présences.');
        }

        if (array_key_exists('heures', $presenceEffective)) {
            $heuresEffectives = array_filter(
                $presenceEffective['heures'],
                fn($h) => array_key_exists('quantite', $h) && !is_null($h['quantite']) && $h['quantite'] > 0
            );
            $heuresEffectivesId = array_filter(array_map(fn($h) => array_key_exists('id', $h) ? $h['id'] : null, $heuresEffectives), fn($h) => !is_null($h));

            // Heures supprimées
            $heuresSupprimeesIds = array_map(
                fn($h) => $h['id'],
                array_filter($heuresReferences, fn($h) => !in_array($h['id'], $heuresEffectivesId))
            );
            if (count($heuresSupprimeesIds) > 0) {
                throw new ArrayException([], 'Permissions insuffisantes pour supprimer des heures.');
            }

            // Heures ajoutées
            $heuresAjoutees = array_filter($heuresEffectives, fn($heure) => !isset($heure['id']) || !$heure['id']);
            foreach ($heuresAjoutees as $heure) {
                throw new ArrayException([], 'Permissions insuffisantes pour ajouter des heures.');
            }

            // Heures modifiées
            $heuresModifiees = array_filter(
                $heuresEffectives,
                fn($heure) => isset($heure['id']) && $heure['id'] && !in_array($heure['id'], $heuresSupprimeesIds)
            );
            foreach ($heuresModifiees as $heure) {
                $heureReference = array_filter(
                    $heuresReferences,
                    fn($h) => $h['id'] == $heure['id']
                );

                // Check qu'aucune heure n'a été modifiée
                if (!$heureReference || $heureReference['quantite'] != $heure['quantite']) {
                    throw new ArrayException([], 'Permissions insuffisantes pour modifier une heure.');
                }
            }
        }
    }

    /**
     * Ajout de sapeurs à un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public static function addSapeurs($exerciceId, $sapeurs)
    {
        // Check pas déjà imputé
        $statut = Exercice::findOrFail($exerciceId)->statut;
        if ($statut == self::EXERCICE_STATUT_ANNULE || $statut > self::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException([], 'Impossible de modifier un exercice déjà imputé');
        }

        // Check sapeur not duplicated
        $ids = array_map(function ($sap) {
            return $sap['sapeur_id'];
        }, ExerciceSapeur::where('exercice_id', $exerciceId)->get()->toArray());

        $sapeurFiltered = array_filter($sapeurs, function ($sap) use ($ids) {
            return !in_array($sap['sapeur_id'], $ids);
        });

        foreach ($sapeurFiltered as $sapeur) {
            self::addSapeurToExercice($exerciceId, $sapeur);

            // Ajout heures sup if any
            $heures = array_filter(
                array_key_exists('heures', $sapeur) ? $sapeur['heures'] : [],
                fn($h) => array_key_exists('quantite', $h) && !is_null($h['quantite']) && $h['quantite'] > 0
            );
            foreach ($heures as $heure) {
                if (!HeureExerciceType::whereId($heure['heure_exercice_type_id'])->exists()) {
                    // On ignore le type d'heure n'existant plus
                    throw new ArrayException(["Message" => "Unknown heure type"]);
                    continue;
                }
                $heure['sapeur_id'] = $sapeur['sapeur_id'];
                self::ajouterHeureExercice($exerciceId, $heure);
            }
        }

        return self::updateStatut($exerciceId);
    }

    public static function addSapeurToExercice($exerciceId, $data)
    {
        $sapeur = new ExerciceSapeur();
        $sapeur->fill($data);
        $sapeur->exercice_id = $exerciceId;
        $sapeur->sapeur_id = $data['sapeur_id'];
        $sapeur->save();
        return $sapeur->toArray();
    }

    public static function ajouterHeureExercice($exerciceId, $heure)
    {
        $type = HeureExerciceType::find($heure['heure_exercice_type_id']);

        $heureExercice = new HeureExercice();
        $heureExercice->fill($type->toArray());
        $heureExercice->fill($heure);
        $heureExercice->exercice_id = $exerciceId;
        $heureExercice->save();

        return $heureExercice;
    }

    /**
     * Modification de sapeurs d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayException
     */
    public static function updateSapeurs($exerciceId, $sapeurs, $hasValidationPermission)
    {
        // FIXME:update sapeurs d'un exercice
        // Check pas déjà imputé
        $statut = Exercice::findOrFail($exerciceId)->statut;

        if (!$hasValidationPermission && $statut >= self::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException([$statut], 'Permissions insuffisantes pour modifier les présences.');
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
                        'absent' => False,
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
                    fn($h) => array_key_exists('quantite', $h) && !is_null($h['quantite']) && $h['quantite'] > 0
                );
                $heuresId = array_filter(array_map(fn($h) => array_key_exists('id', $h) ? $h['id'] : null, $heures), fn($h) => !is_null($h));

                // Heures supprimées
                $heuresSupprimeesId = array_map(fn($h) => $h['id'], array_filter($cachedHeures, fn($h) => $h['sapeur_id'] == $sapeur['sapeur_id'] && !in_array($h['id'], $heuresId)));
                if (count($heuresSupprimeesId) > 0) {
                    throw new ArrayException([], 'Permissions insuffisantes pour modifier les présences.');
                }

                // Heures ajoutées
                $heuresAjoutees = array_filter($heures, fn($heure) => !isset($heure['id']) || !$heure['id']);
                foreach ($heuresAjoutees as $heure) {
                    if (!array_key_exists('heure_exercice_type_id', $heure)) {
                        // On ignore l'heure invalide
                        continue;
                    }
                    throw new ArrayException([], 'Permissions insuffisantes pour modifier les présences.');
                }

                // Heures modifiées
                $heuresModifiees = array_filter($heures, fn($heure) => isset($heure['id']) && $heure['id'] && !in_array($heure['id'], $heuresSupprimeesId));
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
                ->whereId($sapeur['id'])
                ->update([
                    'convoque' => $sapeur['convoque'],
                    'present' => $sapeur['present'],
                    'absent' => $sapeur['absent'],
                    'remplace' => $sapeur['remplace'],
                    'excuse_type_id' => $sapeur['excuse_type_id'],
                    ...(array_key_exists('excuse_statut', $sapeur) ? ['excuse_statut' => $sapeur['excuse_statut']] : [])
                ]);

            $heures = array_filter(
                array_key_exists('heures', $sapeur) ? $sapeur['heures'] : [],
                fn($h) => array_key_exists('quantite', $h) && !is_null($h['quantite']) && $h['quantite'] > 0
            );
            $heuresId = array_filter(array_map(fn($h) => array_key_exists('id', $h) ? $h['id'] : null, $heures), fn($h) => !is_null($h));

            // Heures supprimées
            $heuresSupprimeesId = array_map(fn($h) => $h['id'], array_filter($cachedHeures, fn($h) => $h['sapeur_id'] == $sapeur['sapeur_id'] && !in_array($h['id'], $heuresId)));
            HeureExercice::where('exercice_id', $exerciceId)
                ->where('sapeur_id', $sapeur['sapeur_id'])
                ->whereIn('id', $heuresSupprimeesId)
                ->delete();

            // Heures ajoutées
            $heuresAjoutees = array_filter($heures, fn($heure) => !isset($heure['id']) || !$heure['id']);
            foreach ($heuresAjoutees as $heure) {
                if (!array_key_exists('heure_exercice_type_id', $heure)) {
                    // On ignore l'heure invalide
                    continue;
                }
                $heure['sapeur_id'] = $sapeur['sapeur_id'];
                self::ajouterHeureExercice($exerciceId, $heure);
            }

            // Heures modifiées
            $heuresModifiees = array_filter($heures, fn($heure) => isset($heure['id']) && $heure['id'] && !in_array($heure['id'], $heuresSupprimeesId));
            foreach ($heuresModifiees as $heure) {
                HeureExercice::where('exercice_id', $exerciceId)
                    ->where('sapeur_id', $sapeur['sapeur_id'])
                    ->whereId($heure['id'])
                    ->update(['quantite' => $heure['quantite']]);
            }
            // throw new ArrayException(['ajoutes' => $heuresAjoutees, 'modifies' => $heuresModifiees, 'supprimes' => $heuresSupprimeesId]);
        }

        return self::updateStatut($exerciceId);
    }

    /**
     * Suppression d'une excuse
     *
     * @param $data
     */
    public static function removeExcuse($sapeurId, $exerciceId, $hasValidationPermission)
    {
        $exerciceSapeur = ExerciceSapeur::with('exercice')->where([
            ['sapeur_id', '=', $sapeurId],
            ['exercice_id', '=', $exerciceId]
        ])->first();

        // Ignore si l'exercice n'existe plus
        if ($exerciceSapeur == NULL) {
            return;
        }

        $exercice = $exerciceSapeur->exercice;

        // Ignore si déjà imputé
        if (!$hasValidationPermission && ($exercice->statut >= self::EXERCICE_STATUT_VALIDE || $exerciceSapeur->excuse_statut != self::EXCUSE_STATUT_A_TRAITER)) {
            throw new ArrayException([$exercice->statut], 'Permissions insuffisantes pour supprimer cette excuse.');
        }

        // Suppression justificatif
        if ($exerciceSapeur->justificatif_path) {
            Storage::delete($exerciceSapeur->justificatif_path);
        }

        // Then add the new one
        $exerciceSapeur->justificatif_path = '';
        $exerciceSapeur->justificatif_filename = '';
        $exerciceSapeur->excuse_type_id = null;
        $exerciceSapeur->date_validation = null;
        $exerciceSapeur->remarque = '';
        $exerciceSapeur->justification = '';
        $exerciceSapeur->save();
        return $exerciceSapeur;
    }

    /**
     * Suppression de sapeurs d'un exercice
     *
     * @param $data
     */
    public static function removeSapeurs($exerciceId, $ids)
    {
        // Check pas déjà imputé
        $statut = Exercice::findOrFail($exerciceId)->statut;
        if ($statut == self::EXERCICE_STATUT_ANNULE || $statut > self::EXERCICE_STATUT_SAISI) {
            throw new ArrayException([], 'Impossible de modifier un exercice déjà imputé');
        }

        // FIXME: Supprimer excuse si existante

        ExerciceSapeur::where('exercice_id', $exerciceId)
            ->whereIn('sapeur_id', $ids)
            ->delete();
        HeureExercice::where('exercice_id', $exerciceId)
            ->whereIn('sapeur_id', $ids)
            ->delete();

        return self::updateStatut($exerciceId);
    }

    public static function supprimerConvocations($sapeurId, $exercicesIds)
    {
        ExerciceSapeur::where('sapeur_id', $sapeurId)
            ->whereIn('exercice_id', $exercicesIds)
            ->delete();
        HeureExercice::whereIn('exercice_id', $exercicesIds)
            ->where('sapeur_id', $sapeurId)
            ->whereHas("exercice", function ($q) {
                // Check pour chaque exercice s'il est possible de supprimer la convocation et donc que l'exercice n'est pas déjà imputé
                $q->where('statut', '>', self::EXERCICE_STATUT_ANNULE)
                    ->where('statut', '<=', self::EXERCICE_STATUT_SAISI);
            })
            ->delete();
        return true;
    }

    public static function createHeure($data, $hasValidationPermission)
    {
        $statut = Exercice::findOrFail($data['exercice_id'])->statut;
        if ($statut == self::EXERCICE_STATUT_ANNULE) {
            throw new ArrayException([], "Impossible de modifier un exercice annulé");
        }
        if ($statut > self::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException([], "Impossible de modifier un exercice déjà imputé");
        }
        if ($statut == self::EXERCICE_STATUT_VALIDE && !$hasValidationPermission) {
            throw new ArrayException([], "Permissions insuffisantes");
        }

        $type = HeureExerciceType::find($data['heure_exercice_type_id']);

        $heure = new HeureExercice();
        $heure->fill($type->toArray());
        $heure->fill($data);
        $heure->save();
        return $heure;
    }

    public static function updateHeure($heureId, $data, $hasValidationPermission)
    {
        $heure = HeureExercice::find($heureId);
        if ($heure == NULL) {
            throw new ArrayException([], "Heure inexistante");
        }
        $statut = Exercice::findOrFail($heure->exercice_id)->statut;
        if ($statut == self::EXERCICE_STATUT_ANNULE) {
            throw new ArrayException([], "Impossible de modifier un exercice annulé");
        }
        if ($statut > self::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException([], "Impossible de modifier un exercice déjà imputé");
        }
        if ($statut == self::EXERCICE_STATUT_VALIDE && !$hasValidationPermission) {
            throw new ArrayException([], "Permissions insuffisantes");
        }

        HeureExercice::whereId($heureId)->limit(1)->update($data);
        return HeureExercice::find($heureId);
    }

    public static function removeHeure($heureId, $hasValidationPermission)
    {
        $heure = HeureExercice::find($heureId);
        if ($heure == NULL) {
            throw new ArrayException([], "Heure inexistante");
        }
        $statut = Exercice::findOrFail($heure->exercice_id)->statut;
        if ($statut == self::EXERCICE_STATUT_ANNULE) {
            throw new ArrayException([], "Impossible de modifier un exercice annulé");
        }
        if ($statut > self::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException([], "Impossible de modifier un exercice déjà imputé");
        }
        if ($statut == self::EXERCICE_STATUT_VALIDE && !$hasValidationPermission) {
            throw new ArrayException([], "Permissions insuffisantes");
        }

        HeureExercice::whereId($heureId)->limit(1)->delete();
    }

    public static function listeSapeurOfExerciceById($exerciceId, $hasPresencePermission = false)
    {
        $champs = [
            'id',
            'created_at',
            'updated_at',
            'sapeur_id',
            'exercice_id',
            'excuse_type_id',
            'convoque',
            'present',
            'remplace',
            'absent',
            'excuse_statut',
            'date_demande',
            'justificatif_path',
            'date_validation',
        ];
        $heures = HeureExercice::where('exercice_id', $exerciceId)->get()->toArray();
        $sapeurs = ExerciceSapeur::where('exercice_id', $exerciceId)
            ->get($hasPresencePermission ? '*' : $champs)->toArray();

        $dictionary = [];
        foreach ($sapeurs as $sapeur) {
            $dictionary[$sapeur['sapeur_id']] = $sapeur;
            $dictionary[$sapeur['sapeur_id']]['heures'] = [];
        }
        foreach ($heures as $heure) {
            if (!array_key_exists($heure['sapeur_id'], $dictionary)) {
                $dictionary[$heure['sapeur_id']] = [
                    'convoque' => False,
                    'present' => False,
                    'absent' => False,
                    'remplace' => False,
                    'excuse_type_id' => null,
                    'heures' => [],
                ];
            }
            $dictionary[$heure['sapeur_id']]['heures'][] = $heure;
        }
        return array_values($dictionary);
    }

    public static function sapeurOfExerciceById($exerciceId, $sapeurId)
    {
        $heures = HeureExercice::where('exercice_id', $exerciceId)
            ->where('sapeur_id', $sapeurId)
            ->get()->toArray();
        $sapeur = ExerciceSapeur::where('exercice_id', $exerciceId)
            ->where('sapeur_id', $sapeurId)
            ->first();

        if (!$sapeur) {
            $sapeur = [
                'convoque' => False,
                'present' => False,
                'absent' => False,
                'remplace' => False,
                'excuse_type_id' => null,
                'heures' => [],
            ];
        } else {
            $sapeur = $sapeur->toArray();
        }
        $sapeur['heures'] = $heures;

        return $sapeur;
    }

    public static function convoquer($exerciceComptableId, array $sapeurIds, string $sisKey)
    {
        $civilites = Civilite::all();
        $localites = Localite::all();
        $categories = ExerciceCategorie::all();
        $params = ConvocationParam::first();

        $exercices = Exercice::with('sapeurs')->where('exercice_comptable_id', $exerciceComptableId)->orderBy('date')->orderBy('heure')->get();
        $sapeurs = array_values(array_unique(
            array_merge(...array_map(
                fn($e) => array_map(fn($c) => $c['sapeur_id'], $e['sapeurs']),
                $exercices->toArray()
            ))
        ));
        if (count($sapeurIds) > 0) {
            $sapeurs = array_intersect($sapeurs, $sapeurIds);
        }
        $sapeurs = Sapeur::whereIn('id', $sapeurs)->orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'civilite_id', 'no_rue', 'rue', 'localite_id']);

        $civilitesMap = [];
        $localitesMap = [];
        $categoriesMap = [];
        $sapeursMap = [];
        $exercicesMap = [];
        foreach ($civilites as $e) {
            $civilitesMap[$e->id] = $e->forme_politesse;
        }
        foreach ($localites as $e) {
            $localitesMap[$e->id] = $e->npa . ' ' . $e->designation;
        }
        foreach ($categories as $e) {
            $categoriesMap[$e->id] = $e->designation;
        }
        foreach ($sapeurs as $e) {
            $sapeur = $e->toArray();
            $sapeur['exercices'] = [];
            $sapeursMap[$e->id] = $sapeur;
        }
        foreach ($exercices as $e) {
            foreach ($e['sapeurs'] as $s) {
                if (array_key_exists(strval($s->sapeur_id), $sapeursMap)) {
                    $sapeursMap[strval($s->sapeur_id)]['exercices'][] = $s->toArray();
                }
            }
            $exercicesMap[$e->id] = $e;
        }

        $logoPath = SisParamBusiness::getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::Convocations,
            [
                "params" => $params ?? [],
                "sapeurs" => $sapeursMap,
                "exercices" => $exercicesMap,
                "civilites" => $civilitesMap,
                "localites" => $localitesMap,
                "categories" => $categoriesMap,
            ],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'convocations.pdf'
        );
    }

    public static function listeAppel($exerciceId, string $sisKey)
    {
        $exercice = Exercice::with(['sapeurs', 'localite'])->findOrFail($exerciceId)->toArray();

        $now = Carbon::now();
        $oneMonthFurther = Carbon::now()->addMonths(1);
        $sapeurs = Sapeur::with([
            'permis',
            'fonctions' => function ($query) use ($oneMonthFurther, $now) {
                $query->where('debut', '<=', $oneMonthFurther)->where(function ($query) use ($now) {
                    $query->where('fin', '=', null)
                        ->orWhere('fin', '>=', $now);
                });
            }
        ])->orderBy('nom_prenom')->get([
                    'id',
                    'nom',
                    'prenom',
                    'actif',
                    'email',
                    'localite_id',
                    'fonction_id',
                    'grade_id',
                    'civilite_id',
                    'date_naissance',
                    'type',
                    'annee_incorporation',
                    DB::raw("CONCAT(nom, ' ', prenom) AS nom_prenom")
                ])->toArray();

        $exercice['sapeurs'] = array_map(function ($s) use ($sapeurs) {
            $id = $s['sapeur_id'];
            $sap = array_values(array_filter($sapeurs, function ($sapeur) use ($id) {
                return $sapeur['id'] == $id;
            }))[0];
            $s['excuse_type_id'] = $s['excuse_type_id'] ?? -1;
            $s['display'] = $sap['nom_prenom'];
            $s['fonction_id'] = $sap['fonction_id'] ?? 0;
            return $s;
        }, array_values($exercice['sapeurs']));

        usort($exercice['sapeurs'], function ($a, $b) {
            return strcmp($a['display'], $b['display']);
        });

        $excuses = ExcuseType::get();
        $excusesMap = [];
        foreach ($excuses as $excuse) {
            $excusesMap[$excuse->id] = $excuse->designation;
        }

        $fonctions = Fonction::get();
        $fonctionsMap = [];
        foreach ($fonctions as $fonction) {
            $fonctionsMap[$fonction->id] = $fonction->nom;
        }

        $logoPath = SisParamBusiness::getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::ListeAppel,
            ["exercice" => $exercice, "fonctions" => $fonctionsMap, "excuses" => $excusesMap],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'liste-appel.pdf'
        );
    }

    public static function listePresence($exerciceId, string $sisKey)
    {
        $exercice = Exercice::with(['sapeurs', 'localite'])->findOrFail($exerciceId)->toArray();

        $now = Carbon::now();
        $oneMonthFurther = Carbon::now()->addMonths(1);
        $sapeurs = Sapeur::with([
            'permis',
            'fonctions' => function ($query) use ($oneMonthFurther, $now) {
                $query->where('debut', '<=', $oneMonthFurther)->where(function ($query) use ($now) {
                    $query->where('fin', '=', null)
                        ->orWhere('fin', '>=', $now);
                });
            }
        ])->orderBy('nom_prenom')->get([
                    'id',
                    'nom',
                    'prenom',
                    'actif',
                    'email',
                    'localite_id',
                    'fonction_id',
                    'grade_id',
                    'civilite_id',
                    'date_naissance',
                    'type',
                    'annee_incorporation',
                    DB::raw("CONCAT(nom, ' ', prenom) AS nom_prenom")
                ])->toArray();

        $exercice['sapeurs'] = array_map(function ($s) use ($sapeurs) {
            $id = $s['sapeur_id'];
            $sap = array_values(array_filter($sapeurs, function ($sapeur) use ($id) {
                return $sapeur['id'] == $id;
            }))[0];
            $s['excuse_type_id'] = $s['excuse_type_id'] ?? -1;
            $s['display'] = $sap['nom_prenom'];
            return $s;
        }, array_values($exercice['sapeurs']));

        usort($exercice['sapeurs'], function ($a, $b) {
            return strcmp($a['display'], $b['display']);
        });

        $excuses = ExcuseType::get();
        $excusesMap = [];
        foreach ($excuses as $excuse) {
            $excusesMap[$excuse->id] = $excuse->designation;
        }

        $logoPath = SisParamBusiness::getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::ListePresence,
            ["exercice" => $exercice, "excuses" => $excusesMap],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'liste-presence.pdf'
        );
    }
}
