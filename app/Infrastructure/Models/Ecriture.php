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
        'tarif_min',
        'tarif_min_pour',
        'taux',
        'taux_description',

        'date',
        'heure',

        'sapeur_id',
        'compte_id',
        'exercice_comptable_id',
        'intervention_id',
        'exercice_id',
        'decompte_id',
        'ecriture_categorie_id',

        'type',
        // Types pour imposition
        // 0. Autre
        // 1. Solde
        // 2. Indemnité
        // 3. Frais forfaitaire
        // 4. Frais effectif
        // 5. Charges AVS/AC

        'module',
        // Module effectifs:
        // 0. Divers
        // 1. Exercice
        // 2. Intervention
        // 3. Frais Annuel
        // 4. Indemnité Annuel
        // 5. AVS
        // 6. Amende
        // 7. Décompte d'heures
        // 8. Cours
        // 9. Remboursement à l'employeur ?

        // TODO: Modules à implémenter
        // cours
        // décompte d'heure
    ];

    protected $casts = [
        'total' => 'decimal:2', 'tarif' => 'decimal:2', 'type_unite_id' => 'integer', 'quantite' => 'decimal:2',
        'solde_min' => 'decimal:2', 'solde_min_pour' => 'decimal:2', 'taux' => 'decimal:2', 'solde' => 'decimal:2',
        'indemnite' => 'decimal:2', 'frais' => 'decimal:2', 'avs' => 'boolean', 'amende' => 'boolean', 'frais_annuel' => 'boolean',
        'indemnite_annuel' => 'boolean', 'compte_id' => 'integer', 'exercice_comptable_id' => 'integer', 'ecriture_categorie_id' => 'integer',
        'sapeur_id' => 'integer', 'intervention_id' => 'integer', 'exercice_id' => 'integer'
    ];
}
