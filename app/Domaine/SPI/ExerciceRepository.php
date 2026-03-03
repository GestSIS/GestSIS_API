<?php


namespace App\Domaine\SPI;


interface ExerciceRepository
{
    public function listExerciceLight(int $exerciceComptableId);

    public function listeSapeurOfExerciceById(int $exerciceId);

    public function listExerciceOfSapeurById(int $exerciceComptableId, int $sapeurId);

    public function updateExerciceById(int $exerciceId, $data);

    public function addSapeurToExercice(int $exerciceId, array $sapeurs);

    public function editSapeurOfExercice(int $exerciceId, array $sapeurs);

    public function removeSapeursFromExercice(int $exerciceId, array $ids);

    public function getExerciceStatutById(int $exerciceId);

    public function supprimerConvocations(int $sapeurId, array $exerciceSapeursIds);
}
