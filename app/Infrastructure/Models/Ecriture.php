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

        'date',
        'heure',

        'sapeur_id',
        'compte_id',
        'exercice_comptable_id',
        'intervention_id',
        'exercice_id',

        'decompte_id',
        'ecriture_categorie_id',

        // Booléen
        'amende',
        'avs',
        'indemnite_annuel',
        'frais_annuel',
    ];
}
