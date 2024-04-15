<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteInterventionType extends Model
{
    protected $fillable = [
        'designation',
        'tarif',
        'tarif_min',
        'tarif_min_pour',
        'taux_weekend',
        'taux_nuit',
        'debut',
        'fin',
        'compte_id',
        'phase_id',
        'type_unite_id',
        'ecriture_categorie_id',
        'par_fonction',
        'type',
        'tarif_pro_rata',
        'tarif_min_pro_rata',
    ];
    protected function casts(): array
    {
        return  [
            'tarif' => 'decimal:2', 'tarif_min_pour' => 'decimal:2', 'taux_weekend' => 'decimal:2', 'taux_nuit' => 'decimal:2',
            'compte_id' => 'integer', 'phase_id' => 'integer', 'type_unite_id' => 'integer', 'ecriture_categorie_id' => 'integer',
            'par_fonction' => 'boolean', 'type' => 'integer', 'tarif_pro_rata' => 'boolean', 'tarif_min_pro_rata' => 'boolean'
        ];
    }

    public function fonctions()
    {
        return $this->hasMany(IndemniteInterventionFonction::class, 'indemnite_int_id');
    }
}
