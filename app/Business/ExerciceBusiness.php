<?php


namespace App\Business;


use App\Contracts\ExerciceRepository;
use App\Exceptions\ArrayValidatorException;
use Illuminate\Database\Eloquent\Collection;

class ExerciceBusiness
{
    //Status:
    // 0 -> Annulé
    // 1 -> A saisir
    // 2 -> En attente de validation
    // 3 -> Disponible pour imputation
    // 4 -> Imputée

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
        $data['statut'] = 1;
        return $this->repository->createExercice($data);
    }

    public function deleteExerciceById($exerciceId)
    {
        //TODO Check status
        $statut = $this->repository->getExerciceStatutById($exerciceId);

        if ($statut < 3) {
            $this->repository->deleteExerciceById($exerciceId);
        }
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
        $test = "";
        foreach ($sapeurs as $sapeur) {
//            $sapeurId = $sapeur['sapeur_id'];

            //TODO Check pas dupliqué
//            if (null !== null) {
//                throw new ArrayValidatorException(array('id' => "Duplicated sapeur"));
//            }
            $test.=$exerciceId."-";

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
    }
}
