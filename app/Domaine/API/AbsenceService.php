<?php

namespace App\Domaine\API;

use App\Domaine\Business\AbsenceBusiness;
use App\Infrastructure\Models\Absence;

class AbsenceService
{
  protected $business;

  public function __construct(AbsenceBusiness $business)
  {
    $this->business = $business;
  }

  public function listeAbsence($exerciceComptableId)
  {
    // TODO: ajout marge de date pour absences
    return Absence::with('sapeurIds')->where([])->get();
  }

  public function ajouterAbsence($data)
  {
    return $this->business->ajouterAbsence($data);
  }

  public function modifierAbsence($groupeId, $data)
  {
    return $this->business->modifierAbsence($groupeId, $data);
  }

  public function supprimerAbsence($groupeId)
  {
    return $this->business->supprimerAbsence($groupeId);
  }

  public function modifierAbsenceSapeurs($groupeId, $sapeurIds)
  {
    return $this->business->modifierAbsenceSapeurs($groupeId, $sapeurIds);
  }
}
