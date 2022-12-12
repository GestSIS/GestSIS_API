<?php


namespace App\Domaine\API;

use App\Domaine\Business\TravauxBusiness;
use App\Infrastructure\Models\Travail;

class TravauxService
{
    protected $business;

    public function __construct(TravauxBusiness $business)
    {
        $this->business = $business;
    }

    public function travaux($exerciceComptableId)
    {
        return Travail::where('exercice_comptable_id', '=', $exerciceComptableId)->get();
    }

    public function ajouter($travaux, $auteurId, $hasSaisieCommunePermission)
    {
        $this->business->ajouter($travaux, $auteurId, $hasSaisieCommunePermission);
    }

    public function modifier($travailId, $travail, $sapeurId)
    {
        $this->business->modifier($travailId, $travail, $sapeurId);
    }

    public function supprimer($travaux, $sapeurId)
    {
        $this->business->supprimer($travaux, $sapeurId);
    }

    public function review($id, $accepte, $justification, $quantite)
    {
        $this->business->review($id, $accepte, $justification, $quantite);
    }
}
