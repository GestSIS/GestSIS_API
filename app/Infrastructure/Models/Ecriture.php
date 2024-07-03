<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Ecriture extends Model
{
    protected $fillable = [
        'designation',
        'complement',
        'total',
        'tarif',
        'type_unite_id',
        'quantite',
        'tarif_min',
        'tarif_pro_rata',
        'tarif_min_pour',
        'tarif_min_pro_rata',
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
        'cours_sapeur_id',

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
        // 3. Frais/Indemnité Annuel
        // 4. AVS
        // 5. Amende
        // 6. Fiches travails
        // 7. Cours
        // 8. Remboursement à l'employeur ?

    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2', 'tarif' => 'decimal:2', 'type_unite_id' => 'integer', 'quantite' => 'decimal:2',
            'tarif_min' => 'decimal:2', 'tarif_min_pour' => 'decimal:2', 'taux' => 'decimal:2',
            'tarif_pro_rata' => 'boolean', 'tarif_min_pro_rata' => 'boolean',
            'module' => 'integer', 'type' => 'integer', 'compte_id' => 'integer', 'exercice_comptable_id' => 'integer', 'ecriture_categorie_id' => 'integer',
            'sapeur_id' => 'integer', 'intervention_id' => 'integer', 'exercice_id' => 'integer', 'cours_sapeur_id' => 'integer',
        ];
    }
}
