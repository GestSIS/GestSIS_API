<?php

namespace App\Collections;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ListeFsspExport implements FromCollection, WithHeadings
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
      ->leftJoin('localites', 'localites.id', '=', 'sapeurs.localite_id')
      ->leftJoin('sapeur_telephone', 'sapeur_telephone.sapeur_id', '=', 'sapeurs.id')
      ->select([
        'sapeurs.id AS id',
        'sapeurs.nom',
        'sapeurs.prenom',
        DB::Raw('CONCAT(sapeurs.rue, \' \', sapeurs.no_rue) AS adresse'),
        DB::Raw('CONCAT(localites.npa, \' \', localites.designation) AS localite'),
        'sapeur_telephone.numero',
        'sapeurs.email',
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
      'Nom',
      'Prénom',
      'Adresse',
      'NPA Localité',
      'Téléphone',
      'Email'
    ];
  }
}
