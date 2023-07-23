<?php

namespace App\Domaine\API;

use App\Domaine\Business\AbsenceBusiness;
use App\Infrastructure\Models\Absence;
use App\Infrastructure\Models\ExerciceComptable;
use Illuminate\Support\Collection;

class AbsenceService
{
  protected $business;

  public function __construct(AbsenceBusiness $business)
  {
    $this->business = $business;
  }

  public function listeAbsence(int $exerciceComptableId): Collection
  {
    $exerciceComptable = ExerciceComptable::find($exerciceComptableId);

    // TODO: ajout marge de date pour absences
    return Absence::where([
      ['debut', '<', $exerciceComptable->fin],
      ['fin', '>', $exerciceComptable->debut]
    ])->get();
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
}
