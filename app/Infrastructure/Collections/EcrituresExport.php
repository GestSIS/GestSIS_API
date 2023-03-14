<?php

namespace App\Infrastructure\Collections;

use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EcrituresExport implements FromQuery, WithHeadings
{
  use Exportable;
  protected $decompteId;

  public function __construct($decompteId)
  {
    $this->decompteId = $decompteId;
  }

  public function query()
  {
    return Ecriture::query()
      ->where('ecritures.decompte_id', '=', $this->decompteId)
      ->leftJoin('sapeurs', 'ecritures.sapeur_id', '=', 'sapeurs.id')
      ->leftJoin('civilites', 'civilites.id', '=', 'sapeurs.civilite_id')
      ->leftJoin('type_unites', 'type_unites.id', '=', 'ecritures.type_unite_id')
      ->leftJoin('comptes', 'comptes.id', '=', 'ecritures.compte_id')
      ->select([
        'sapeurs.nom', 'sapeurs.prenom', 'sapeurs.suffixe',
        'ecritures.designation', 'ecritures.date', 'ecritures.heure', 'ecritures.complement',
        'ecritures.total', 'ecritures.quantite', 'type_unites.unite', 'ecritures.tarif',
        'ecritures.tarif_min', 'ecritures.tarif_min_pour',
        'ecritures.taux', 'ecritures.taux_description',
        'comptes.designation AS compte_designation', 'comptes.numero AS compte_numero',
        DB::Raw("CASE ecritures.type WHEN 0 THEN 'autre' WHEN 1 THEN 'solde' WHEN 2 THEN 'indemnite' WHEN 3 THEN 'frais forfaitaire' WHEN 4 THEN 'frais effectif' WHEN 5 THEN 'charges AVS/AC' END")
      ]);

    // 'module',
  }

  public function headings(): array
  {
    // Put Here Header Name That you want in your excel sheet 
    return [
      'nom', 'prenom', 'suffixe',
      'ecriture', 'date', 'heure', 'complement',
      'total', 'quantite', 'unite', 'tarif',
      'tarif_min', 'tarif_min_pour',
      'taux', 'taux_description',
      'compte_designation', 'compte_numero',
      'type'
    ];
  }
}
