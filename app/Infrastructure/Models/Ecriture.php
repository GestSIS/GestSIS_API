<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Ecriture extends Model
{
    protected $fillable = [
        'designation',
        'total',
        'tarif',
        'type_unite_id',
        'quantite',
        'solde_min',
        'solde_min_pour',
        'taux',
        'solde',
        'indemnite',
        'frais',
        'sapeur_id',
        'compte_id',
        'exercice_comptable_id',
        'intervention_id',
        'exercice_id',
        'indemnite_annuel_type_id',
        'frais_annuel_type_id',
        'ecriture_categorie_id',
        'date',
        'heure',
        'date_paiement',
        'id_paiement'
    ];

}
