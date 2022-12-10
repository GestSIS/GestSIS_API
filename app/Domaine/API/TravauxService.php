<?php


namespace App\Domaine\API;

use App\Domaine\Business\InterventionBusiness;
use App\Domaine\SPI\InterventionRepository;

class TravauxService
{
    protected $business;

    public function __construct(InterventionBusiness $business)
    {
        $this->business = $business;
    }

    public function ajout($travaux)
    {
        // TODO: check status du travail
    }

    public function modifier($travaux)
    {
        // TODO: check status du travail
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
