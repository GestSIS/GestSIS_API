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

    public function travaux($exerciceComptableId, $sapeurId, $withEcritures)
    {
        $query = Travail::where('exercice_comptable_id', '=', $exerciceComptableId);

        if ($sapeurId != null) {
            $query = $query->with('ecritures');
        }
        if ($sapeurId != null) {
            $query =  $query
                ->where(function ($query) use ($sapeurId) {
                    $query->where('auteur_id', '=', $sapeurId)
                        ->orWhere('sapeur_id', '=', $sapeurId);
                });
        }
        return $query->get();
    }

    public function ajouter($travaux, $auteurId, $hasSaisieCommunePermission)
    {
        return $this->business->ajouter($travaux, $auteurId, $hasSaisieCommunePermission);
    }

    public function modifier($travailId, $travail, $sapeurId)
    {
        return $this->business->modifier($travailId, $travail, $sapeurId);
    }

    public function supprimer($travaux, $sapeurId)
    {
        return $this->business->supprimer($travaux, $sapeurId);
    }

    public function review($id, $accepte, $justification, $quantite)
    {
        return $this->business->review($id, $accepte, $justification, $quantite);
    }

    public function cancelReview($id)
    {
        return $this->business->cancelReview($id);
    }
}
