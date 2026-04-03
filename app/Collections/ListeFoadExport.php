<?php

namespace App\Collections;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ListeFoadExport implements FromCollection, WithHeadings
{
  use Exportable;

  protected $date;

  public function __construct($date)
  {
    $this->date = $date;
  }

  public function collection()
  {
    $date = $this->date;
    return Sapeur::query()
      ->where('sapeurs.type', '=', SapeurBusiness::TYPE_SAPEUR)
      ->leftJoin('mutations', 'sapeurs.id', '=', 'mutations.sapeur_id')
      ->leftJoin('sapeur_telephone', 'sapeur_telephone.sapeur_id', '=', 'sapeurs.id')
      ->leftJoin('grades', 'sapeurs.grade_id', '=', 'grades.id')
      ->select([
        'sapeurs.nom',
        'sapeurs.prenom',
        'sapeur_telephone.numero',
        'sapeurs.email',
        DB::raw('CASE WHEN `grades`.`groupe`=1 THEN "Officier" WHEN `grades`.`groupe`=2 THEN "Sous-Officier" ELSE "Sapeur" END')
      ])
      ->where('mutations.incorporation', '<=', $date)
      ->where(function ($query) use ($date) {
        $query->where('mutations.sortie', '=', null)
          ->orWhere('mutations.sortie', '>=', $date);
      })
      ->orderBy('sapeurs.nom', 'ASC')
      ->orderBy('sapeurs.prenom', 'ASC')
      ->get();
  }

  public function headings(): array
  {
    //Put Here Header Name That you want in your excel sheet 
    return [
      'Nom',
      'Prénom',
      'Téléphone',
      'Email',
      'Grade'
    ];
  }
}
