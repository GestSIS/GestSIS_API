<?php


namespace App\Business;


use App\Contracts\ExerciceRepository;
use App\Exceptions\ArrayValidatorException;
use Illuminate\Database\Eloquent\Collection;

class ExerciceBusiness
{
    /**
     * TODO REFACTOR THIS CLASS TO FIT NEW STRUCTURE
     *
     */

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
     * @throws ArrayValidatorException
     */
    public function createExercice($data)
    {

        $this->repository->createExercice($data);
    }

    /**
     * Ajout de sapeurs d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException
     */
    public function addSapeurs($exerciceId, $sapeurs)
    {
        //TODO INSIDE BUSINESS
        //TODO Check not impute

        //TODO Check sapeur not duplicated
        $saps = $this->repository->listSapeurOfExerciceById($exerciceId);

        foreach ($sapeurs as $sapeur) {
//            $sapeurId = $sapeur['sapeur_id'];

            //TODO Check pas dupliqué
//            if (null !== null) {
//                throw new ArrayValidatorException(array('id' => "Duplicated sapeur"));
//            }

            $this->repository->addSapeurToExercice($exerciceId, $sapeur);
        }
    }

    /**
     * Modification de sapeurs d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException
     */
    public function updateSapeurs($exerciceId, $sapeurs)
    {
        //TODO Check pas imputé
        foreach ($sapeurs as $sapeur) {
            $this->repository->editSapeurOfExercice($exerciceId, $sapeur);
        }
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
    }
}
