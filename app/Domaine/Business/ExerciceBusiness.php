<?php

namespace App\Domaine\Business;

use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\Exceptions\ArrayException;
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
        if ($statut == self::EXERCICE_STATUT_ANNULE || $statut > self::EXERCICE_STATUT_SAISI) {
            return $statut;
        }

        // Check saisi des présences sont saisies
        $presences = $this->repository->listSapeurOfExerciceById($exerciceId);
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

    public function deleteExerciceById($exerciceId)
    {
        // Check pas déjà imputé
        $statut = $this->repository->getExerciceStatutById($exerciceId);
        if ($statut > self::EXERCICE_STATUT_IMPUTE) {
            throw new ArrayException(['message' => 'Impossible de supprimer un exercice déjà imputé']);
        }

        $this->repository->deleteExerciceById($exerciceId);
    }

    public function validateExercice($exerciceId)
    {
        $statut = $this->repository->getExerciceStatutById($exerciceId);
        if ($statut !== self::EXERCICE_STATUT_SAISI) {
            throw new ArrayException(["message" => "Impossible de valider l'exercice."]);
        }

        // Check saisi des présences sont saisies
        $presences = $this->repository->listSapeurOfExerciceById($exerciceId);
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
            throw new ArrayException(['message' => 'Impossible de modifier un exercice déjà imputé']);
        }

        // Check sapeur not duplicated
        $ids = array_map(function ($sap) {
            return $sap->sapeur_id;
        }, $this->repository->listSapeurOfExerciceById($exerciceId));

        $sapeurFiltered = array_filter($sapeurs, function ($sap) use ($ids) {
            return !in_array($sap['sapeur_id'], $ids);
        });

        foreach ($sapeurFiltered as $sapeur) {
            $this->repository->addSapeurToExercice($exerciceId, $sapeur);
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
    public function updateSapeurs($exerciceId, $sapeurs)
    {
        // Check pas déjà imputé
        $statut = $this->repository->getExerciceStatutById($exerciceId);
        if ($statut == self::EXERCICE_STATUT_ANNULE || $statut > self::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException(['message' => 'Impossible de modifier un exercice déjà imputé']);
        }

        foreach ($sapeurs as $sapeur) {
            $this->repository->editSapeurOfExercice($exerciceId, $sapeur);
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
            throw new ArrayException(['message' => 'Impossible de modifier un exercice déjà imputé']);
        }

        $this->repository->removeSapeursFromExercice($exerciceId, $ids);

        return $this->updateStatut($exerciceId);
    }

    public function supprimerConvocations($sapeurId, $exerciceSapeursIds)
    {
        // TODO: Check pour chaque exercice s'il est possible de supprimer la convocation
        // et donc que l'exercice n'est pas déjà imputé
        $this->repository->supprimerConvocations($sapeurId, $exerciceSapeursIds);
        return true;
    }
}
