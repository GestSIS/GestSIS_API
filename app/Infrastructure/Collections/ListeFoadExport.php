<?php

namespace App\Infrastructure\Collections;

use App\Domaine\Business\SapeurBusiness;
use App\Infrastructure\Models\Sapeur;
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
    $data = Sapeur::query()
      ->where('sapeurs.type', '=', SapeurBusiness::TYPE_SAPEUR)
      ->leftJoin('mutations', 'sapeurs.id', '=', 'mutations.sapeur_id')
      ->leftJoin('sapeur_telephone', 'sapeur_telephone.sapeur_id', '=', 'sapeurs.id')
      # TODO: Grade principal
      ->leftJoin('grade_sapeurs', 'sapeur_telephone.sapeur_id', '=', 'sapeurs.id')
      ->leftJoin('grades', 'sapeur_telephone.sapeur_id', '=', 'sapeurs.id')
      ->select([
        'sapeurs.nom', 'sapeurs.prenom',
        'sapeur_telephone.numero', 'sapeurs.email',
      ])
      ->where('mutations.incorporation', '<=', $date)
      ->where(function ($query) use ($date) {
        $query->where('mutations.sortie', '=', null)
          ->orWhere('mutations.sortie', '>=', $date);
      })
      ->orderBy('sapeurs.nom', 'ASC')
      ->orderBy('sapeurs.prenom', 'ASC')
      ->get()
      ->all();

    return collect(
      array_values(
        array_reduce($data, function ($acc, $e) {
          $id = $e->id;
          unset($e->id);
          $present = $acc[$id] ?? false;
          if (!$present)
            $acc[$id] = $e;
          return $acc;
        }, [])
      )
    );
  }

  public function headings(): array
  {
    //Put Here Header Name That you want in your excel sheet 
    return [
      'Nom', 'Prénom', 'Adresse', 'NPA Localité', 'Téléphone', 'Email'
    ];
  }
}
