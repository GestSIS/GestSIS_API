<?php


namespace App\Services;


use App\Business\ExerciceBusiness;
use App\Contracts\ExerciceRepository;
use App\Exceptions\ArrayValidatorException;
use App\Models\Exercice;
use Illuminate\Database\Eloquent\Collection;

class ExerciceService
{
    protected $repository;
    protected $business;

    public function __construct(ExerciceRepository $repository, ExerciceBusiness $business)
    {
        $this->repository = $repository;
        $this->business = $business;
    }

    public function getExerciceById($exerciceId)
    {
        return $this->repository->getExerciceByIdWith($exerciceId, ['sapeurs']);
    }

    public function listeExercice()
    {
        return $this->repository->listExerciceLight();
    }

    public function listSapeurOfExerciceById($exerciceId)
    {
        return $this->repository->listSapeurOfExerciceById($exerciceId);
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
        return $this->business->createExercice($data);
    }

    /**
     * Updates a post.
     *
     * @param int
     * @param array
     * @return Exercice
     * @throws ArrayValidatorException
     */
    public function updatExercice($exerciceId, $data)
    {
        return $this->repository->updateExercicebyId($exerciceId, $data);
    }

    public function deleteExerciceById($exerciceId)
    {
        $this->business->deleteExerciceById($exerciceId);
    }

    public function validateExercice($exerciceId)
    {
        return $this->business->validateExercice($exerciceId);
    }

    /**
     * Ajout de sapeurs à un exercice
     *
     * @param $sapeurs
     * @throws ArrayValidatorException
     * @return array
     */
    public function addSapeurs($exerciceId, $sapeurs)
    {
        $statut = $this->business->addSapeurs($exerciceId, $sapeurs);
        return [
            "statut" => $statut,
            "sapeurs" => $this->repository->listSapeurOfExerciceById($exerciceId)
        ];
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
        $this->business->updateSapeurs($exerciceId, $sapeurs);
        return $this->repository->listSapeurOfExerciceById($exerciceId);
    }

    /**
     * Suppression de sapeurs d'un exercice
     *
     * @param $data
     * @return statut
     */
    public function removeSapeurs($exerciceId, $ids)
    {
        return $this->business->removeSapeurs($exerciceId, $ids);
    }
}
