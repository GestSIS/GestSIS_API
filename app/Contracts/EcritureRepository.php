<?php


namespace App\Contracts;


interface EcritureRepository
{
    public function listeEcritureForExercice($exercice_id);

    public function listeEcritureForIntervention($intervention_id);

    public function listeFraisAnnuelByExeComptableId($exerciceComptableId);

    public function listeIndemniteAnnuelByExeComptableId($exerciceComptableId);

    function getEcrituresForExerciceById($exerciceId);

    function getEcrituresForInterventionById($interventionId);

    function getEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);

    public function persisteNewEcriture($ecriture);
}
