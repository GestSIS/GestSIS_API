<?php

namespace App\Infrastructure\Collections;

use App\Infrastructure\Models\Sapeur;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SapeursExport implements FromQuery, WithHeadings
{
  use Exportable;
  protected $ids;

  public function __construct($sapeursIds)
  {
    $this->ids = $sapeursIds;
  }

  public function query()
  {
    $query = Sapeur::query();

    // Sélection des sapeurs
    if (count($this->ids) == 0) {
      $query = $query->where('sapeurs.actif', True);
    } else {
      $query = $query->whereIn('sapeurs.id', $this->ids);
    }

    return $query->leftJoin('civilites', 'civilites.id', '=', 'sapeurs.civilite_id')
      ->leftJoin('fonctions', 'fonctions.id', '=', 'sapeurs.fonction_id')
      ->leftJoin('grades', 'grades.id', '=', 'sapeurs.grade_id')
      ->leftJoin('localites', 'localites.id', '=', 'sapeurs.localite_id')
      ->select([
        'civilites.forme_politesse',
        'sapeurs.nom', 'sapeurs.prenom', 'sapeurs.suffixe', 'sapeurs.rue', 'sapeurs.no_rue', 'sapeurs.date_naissance', 'sapeurs.no_avs', 'sapeurs.profession', 'sapeurs.employeur',
        'sapeurs.lieu_de_travail', 'sapeurs.email', 'sapeurs.actif', 'sapeurs.iban', 'sapeurs.remarque',
        'localites.npa as npa', 'localites.designation as localite',
        'grades.designation as grade_nom', 'grades.abreviation as grade_abr',
        'fonctions.nom as fonction_nom', 'fonctions.abreviation as fonction_abr'
      ]);
  }

  public function headings(): array
  {
    //Put Here Header Name That you want in your excel sheet 
    return [
      'civilite',
      'nom', 'prenom', 'suffixe', 'rue', 'no_rue', 'date_naissance', 'no_avs', 'profession', 'employeur',
      'lieu_de_travail', 'email', 'actif', 'iban', 'remarque',
      'npa', 'localite',
      'grade', 'grade_abr',
      'fonction', 'fonction_abr'
    ];
  }
}
