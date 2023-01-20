<?php

namespace App\Infrastructure\Collections;

use App\Infrastructure\Models\Sapeur;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SapeursExport implements FromCollection, WithHeadings
{
  use Exportable;
  protected $ids;

  public function __construct($sapeursIds)
  {
    $this->ids = $sapeursIds;
  }

  public function collection()
  {
    $query = Sapeur::query();

    // Sélection des sapeurs
    if (count($this->ids) == 0) {
      $query = $query->where('sapeurs.actif', True);
    } else {
      $query = $query->whereIn('sapeurs.id', $this->ids);
    }

    $data = $query->leftJoin('civilites', 'civilites.id', '=', 'sapeurs.civilite_id')
      ->leftJoin('fonctions', 'fonctions.id', '=', 'sapeurs.fonction_id')
      ->leftJoin('grades', 'grades.id', '=', 'sapeurs.grade_id')
      ->leftJoin('localites', 'localites.id', '=', 'sapeurs.localite_id')
      ->leftJoin('sapeur_telephone', 'sapeur_telephone.sapeur_id', '=', 'sapeurs.id')
      ->select([
        'sapeurs.id as id',
        'civilites.forme_politesse',
        'sapeurs.nom', 'sapeurs.prenom', 'sapeurs.suffixe', 'sapeurs.rue', 'sapeurs.no_rue', 'sapeurs.date_naissance', 'sapeurs.no_avs', 'sapeurs.profession', 'sapeurs.employeur',
        'sapeurs.lieu_de_travail', 'sapeurs.email', 'sapeurs.actif', 'sapeurs.iban', 'sapeurs.remarque',
        'localites.npa as npa', 'localites.designation as localite',
        'grades.designation as grade_nom', 'grades.abreviation as grade_abr',
        'fonctions.nom as fonction_nom', 'fonctions.abreviation as fonction_abr',
        'sapeur_telephone.numero as tel_numero', 'sapeur_telephone.telephone_type_id as tel_type', 'sapeur_telephone.priorite as tel_priorite',
      ])
      ->orderBy('sapeurs.nom', 'ASC')
      ->orderBy('sapeurs.prenom', 'ASC')
      ->get()->all();

    return collect(
      array_values(
        array_reduce($data, function ($acc, $e) {
          $id = $e->id;
          $telNumero = $e->tel_numero;
          $telType = $e->tel_type;
          // $telPriorite = $e->tel_priorite;

          unset($e->id);
          $e->tel_numero = null;
          $e->tel_type = null;
          $e->tel_priorite = null;

          $present = $acc[$id] ?? false;
          if (!$present)
            $acc[$id] = $e;

          $elem = $acc[$id];

          if (intval($telType) == 1)
            $elem->tel_numero = $telNumero;
          if (intval($telType) == 2)
            $elem->tel_type = $telNumero;
          if (intval($telType) == 3)
            $elem->tel_priorite = $telNumero;
          $acc[$id] = $elem;
          return $acc;
        }, [])
      )
    );
  }

  public function headings(): array
  {
    // Put Here Header Name That you want in your excel sheet 
    return [
      'civilite',
      'nom', 'prenom', 'suffixe', 'rue', 'no_rue', 'date_naissance', 'no_avs', 'profession', 'employeur',
      'lieu_de_travail', 'email', 'actif', 'iban', 'remarque',
      'npa', 'localite',
      'grade', 'grade_abr',
      'fonction', 'fonction_abr',
      'tel_prive', 'tel_profesionnel', 'tel_portable',
    ];
  }
}
