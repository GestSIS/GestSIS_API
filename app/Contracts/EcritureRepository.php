<?php


namespace App\Contracts;


use App\Models\Ecriture;

interface EcritureRepository
{
    public function listeEcritureForExercice($exercice_id);
    public function listeEcritureForIntervention($intervention_id);

    public function listeFraisAnnuelByExeComptableId($exerciceComptableId);
    public function listeIndemniteAnnuelByExeComptableId($exerciceComptableId);

    public function persisteNewEcriture($ecriture);
}
