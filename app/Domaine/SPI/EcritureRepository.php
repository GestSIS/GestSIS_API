<?php


namespace App\Domaine\SPI;


interface EcritureRepository
{
    public function listeAllEcritureForExerciceComptableById($exerciceComptableId);

    public function listeEcritureForCompteAndExerciceComptableById($compteId, $exerciceComptableId);

    public function listeAmendeForExerciceComptableById($exerciceComptableId);

    public function listeEcritureDiversForExerciceComptableById($exerciceComptableId);

    public function listeEcritureForExercice($exerciceId);

    public function listeEcritureForIntervention($interventionId);

    public function listeEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);

    public function persisteNewEcriture($ecriture);

    public function computeEcritureForPersonalDecompte($exerciceComptableId);
}
