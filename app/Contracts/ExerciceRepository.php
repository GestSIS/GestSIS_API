<?php


namespace App\Contracts;


interface ExerciceRepository
{
    public function listExerciceLight();

    public function listSapeurOfExerciceById($exerciceId);

    public function getExerciceByIdWith($exerciceId, $with = []);

    public function createExercice(array $data);

    public function updateExerciceById($exerciceId, $data);

    public function addSapeurToExercice($exerciceId, $sapeurs);

    public function editSapeurOfExercice($exerciceId, $sapeurs);

    public function removeSapeursFromExercice($exerciceId, $ids);

    public function getExerciceStatutById($exerciceId);

    public function deleteExerciceById($exerciceId);
}
