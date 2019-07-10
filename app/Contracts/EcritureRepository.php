<?php


namespace App\Contracts;


interface EcritureRepository
{
    public function listeEcritureForCompteAndExerciceComptableById($compteId, $exerciceComptableId);

    public function listeEcritureForExercice($exerciceId);

    public function listeEcritureForIntervention($interventionId);

    public function listeEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);

    public function listeFraisAnnuelByExeComptableId($exerciceComptableId);

    public function listeIndemniteAnnuelByExeComptableId($exerciceComptableId);

    public function persisteNewEcriture($ecriture);

    public function computeEcritureForPersonalDecompte($exerciceComptableId);
}
