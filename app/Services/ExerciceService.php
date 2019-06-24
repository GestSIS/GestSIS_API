<?php


namespace App\Services;


use App\Business\ExerciceBusiness;
use App\Contracts\ExerciceRepository;
use App\Exceptions\ArrayValidatorException;
use App\Models\Exercice;
use Illuminate\Database\Eloquent\Collection;

class ExerciceService
{
    /**
     * Get's a exercice by it's ID
     *
     * @param int
     * @return ExerciceBusiness
     */
    public function get(ExerciceRepository $test, $exercice_id)
    {
        return $test->find('$exercice_id');
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

    }

    /**
     * Updates a post.
     *
     * @param int
     * @param array
     * @return Exercice
     * @throws ArrayValidatorException
     */
    public function update($data)
    {

    }

    /**
     * Delete a exercice.
     *
     * @param int
     */
    public function delete($exercice_id)
    {

    }

    /**
     * Ajout de sapeurs d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException
     */
    public function addSapeurs($data)
    {

    }

    /**
     * Modification de sapeurs d'un exercice
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException
     */
    public function updateSapeurs($data)
    {

    }

    /**
     * Suppression de sapeurs d'un exercice
     *
     * @param $data
     */
    public function removeSapeurs($data)
    {

    }
}
