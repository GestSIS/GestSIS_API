<?php


namespace App\Contracts;


interface EcritureRepository
{
    public function listeEcritureForExercice($exercice_id);

    public function listeEcritureForIntervention($intervention_id);

    public function listeFraisAnnuelByExeComptableId($exerciceComptableId);

    public function listeIndemniteAnnuelByExeComptableId($exerciceComptableId);

    public function getEcrituresForExerciceById($exerciceId);

    public function getEcrituresForInterventionById($interventionId);

    public function getEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);

    public function persisteNewEcriture($ecriture);
}
