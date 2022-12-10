<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Travail;

class TravauxBusiness
{
    public function ajout($travaux)
    {
        // TODO: check status du travail
    }

    public function modifier($travailId, $travaux)
    {
        // TODO: check status du travail
        $travail = Travail::find($travailId);
        // $travail->fill($trava);
    }

    public function supprimer($travaux)
    {
        // TODO: check status du travail
    }

    public function valider($id, $status, $justification)
    {
        // TODO: check status du travail
    }
}
