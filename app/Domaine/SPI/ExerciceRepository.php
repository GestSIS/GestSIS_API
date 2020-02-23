<?php


namespace App\Domaine\SPI;


interface ExerciceRepository
{
    public function listExerciceLight();

    public function listSapeurOfExerciceById(int $exerciceId);

    public function listExerciceOfSapeurById(int $exerciceComptableId, int $sapeurId);

    public function getExerciceByIdWith(int $exerciceId, $with = []);

    public function createExercice(array $data);

    public function updateExerciceById(int $exerciceId, $data);

    public function addSapeurToExercice(int $exerciceId, array $sapeurs);

    public function editSapeurOfExercice(int $exerciceId, array $sapeurs);

    public function removeSapeursFromExercice(int $exerciceId, array $ids);

    public function getExerciceStatutById(int $exerciceId);

    public function deleteExerciceById(int $exerciceId);

    public function supprimerConvocations(int $sapeurId, array $exerciceSapeursIds);
}
