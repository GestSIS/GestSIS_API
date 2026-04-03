<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvsParam extends Model
{
    protected $fillable = ['taux_avs', 'taux_ac', 'franchise_avs', 'franchise_imposition', 'franchise_imposition_cantonale', 'compte_id', 'ecriture_categorie_id'];
    protected function casts(): array
    {
        return [
            'taux_avs' => 'decimal:5',
            'taux_ac' => 'decimal:5',
            'franchise_avs' => 'decimal:2',
            'franchise_imposition' => 'decimal:2',
            'franchise_imposition_cantonale' => 'decimal:2',
            'compte_id' => 'integer',
            'ecriture-categorie_id' => 'integer'
        ];
    }
}
