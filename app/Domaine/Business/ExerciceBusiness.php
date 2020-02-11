<?php


namespace App\Domaine\Business;

use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\Exceptions\ArrayException;
use Illuminate\Database\Eloquent\Collection;

class ExerciceBusiness
{
    //Status:
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
     * Create a exercice
     *
     * @param $data
     * @return ExerciceBusiness
     * @throws ArrayException
     */
    public function createExercice($data)
    {
        // Statut:
        // 1 -> a saisir
        // 2 -> a valider
        // 3 -> a imputer
        // 4 -> imputée
        $data['statut'] = 1; //TODO: Ajouter ça dans paramètres ?
        return $this->repository->createExercice($data);
    }

    public function deleteExerciceById($exerciceId)
    {
        //TODO Check status
        $statut = $this->repository->getExerciceStatutById($exerciceId);

        if ($statut < self::EXERCICE_STATUT_VALIDE) {
            $this->repository->deleteExerciceById($exerciceId);
        }
    }

    public function validateExercice($exerciceId)
    {
        //TODO Check saisi des présences sont saisies
        $statut = $this->repository->getExerciceStatutById($exerciceId);
        if ($statut === self::EXERCICE_STATUT_SAISI) {
            return $this->repository->updateExerciceById($exerciceId, [
                "statut" => self::EXERCICE_STATUT_VALIDE
            ]);
        }
        throw new ArrayException(["message" => "Impossible de valider l'exercice."]);
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
        //TODO INSIDE BUSINESS
        //TODO Check not impute

        //TODO Check sapeur not duplicated
        $saps = $this->repository->listSapeurOfExerciceById($exerciceId);
        $test = "";
        foreach ($sapeurs as $sapeur) {
//            $sapeurId = $sapeur['sapeur_id'];

            //TODO Check pas dupliqué
//            if (null !== null) {
//                throw new ArrayException(array('id' => "Duplicated sapeur"));
//            }
            $test .= $exerciceId . "-";

            $this->repository->addSapeurToExercice($exerciceId, $sapeur);
        }

        $statut = $this->repository->getExerciceStatutById($exerciceId);
        $this->repository->updateExerciceById($exerciceId, array("statut" => max($statut, self::EXERCICE_STATUT_SAISI)));

        return $statut;
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
        //TODO Check pas imputé
        foreach ($sapeurs as $sapeur) {
            $this->repository->editSapeurOfExercice($exerciceId, $sapeur);
        }

        //TODO Si tous les sapeurs ont étés saisi passer un mode en attente de validation
    }

    /**
     * Suppression de sapeurs d'un exercice
     *
     * @param $data
     */
    public function removeSapeurs($exerciceId, $ids)
    {
        //TODO Check pas imputé
        $this->repository->removeSapeursFromExercice($exerciceId, $ids);

        $statut = $this->repository->getExerciceStatutById($exerciceId);

        if (count($this->repository->listSapeurOfExerciceById($exerciceId)) === 0) {
            $statut = $this->repository->updateExerciceById($exerciceId, ["statut" => self::EXERCICE_STATUT_EMPTY])->statut;
        }

        return $statut;
    }
}
