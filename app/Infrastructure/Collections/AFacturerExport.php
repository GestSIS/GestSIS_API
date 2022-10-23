<?php

namespace App\Infrastructure\Collections;

use App\Infrastructure\Models\Decompte;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AFacturerExport implements FromQuery, WithHeadings
{
  use Exportable;
  protected $decompteId;

  public function __construct($decompteId)
  {
    $this->decompteId = $decompteId;
  }

  public function query()
  {

    // TODO: Charger le décompte avec ses paiements négatif
    return Decompte::query()
      ->where('decomptes.id', '=', $this->decompteId)
      ->leftJoin('paiements', 'decomptes.id', '=', 'paiements.decompte_id')
      ->where('paiements.total', '<', 0.0)
      ->leftJoin('sapeurs', 'paiements.sapeur_id', '=', 'sapeurs.id')
      ->leftJoin('civilites', 'civilites.id', '=', 'sapeurs.civilite_id')
      ->leftJoin('localites', 'localites.id', '=', 'sapeurs.localite_id')
      ->select([
        'civilites.forme_politesse',
        'sapeurs.nom', 'sapeurs.prenom', 'sapeurs.suffixe', 'sapeurs.rue', 'sapeurs.no_rue', 'sapeurs.date_naissance', 'sapeurs.no_avs',
        'sapeurs.email', 'sapeurs.actif', 'sapeurs.remarque',
        'localites.npa as npa', 'localites.designation as localite',
        'decomptes.designation as nom_decompte', 'decomptes.date as decompte_date', 'paiements.total'
      ]);
  }

  public function headings(): array
  {
    // Put Here Header Name That you want in your excel sheet 
    return [
      'civilite',
      'nom', 'prenom', 'suffixe', 'rue', 'no_rue', 'date_naissance', 'no_avs',
      'email', 'actif', 'remarque',
      'npa', 'localite',
      'nom_decompte', 'decompte_date', 'montant_a_facturer'
    ];
  }
}
