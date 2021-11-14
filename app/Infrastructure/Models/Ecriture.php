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

    protected $casts = [
        'total' => 'decimal:2', 'tarif' => 'decimal:2', 'type_unite_id' => 'integer', 'quantite' => 'decimal:2',
        'solde_min' => 'decimal:2', 'solde_min_pour' => 'decimal:2', 'taux' => 'decimal:2', 'solde' => 'decimal:2',
        'indemnite' => 'decimal:2', 'frais' => 'decimal:2', 'avs' => 'boolean', 'amende' => 'boolean', 'frais_annuel' => 'boolean',
        'indemnite_annuel' => 'boolean', 'compte_id' => 'integer', 'exercice_comptable_id' => 'integer', 'ecriture_categorie_id' => 'integer',
        'sapeur_id' => 'integer', 'intervention_id' => 'integer', 'exercice_id' => 'integer'
    ];
}
