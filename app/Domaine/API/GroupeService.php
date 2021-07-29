<?php

namespace App\Domaine\API;

use App\Domaine\Business\OrganisationBusiness;
use App\Infrastructure\Models\Groupe;

class GroupeService
{
  protected $business;

  public function __construct(OrganisationBusiness $business)
  {
    $this->business = $business;
  }

  public function listeGroupe()
  {
    return Groupe::with('sapeurIds')->get();
  }

  public function ajouterGroupe($data)
  {
    return $this->business->ajouterGroupe($data);
  }

  public function modifierGroupe($groupeId, $data)
  {
    return $this->business->modifierGroupe($groupeId, $data);
  }

  public function supprimerGroupe($groupeId)
  {
    return $this->business->supprimerGroupe($groupeId);
  }

  public function modifierGroupeSapeurs($groupeId, $sapeurIds)
  {
    return $this->business->modifierGroupeSapeurs($groupeId, $sapeurIds);
  }
}
