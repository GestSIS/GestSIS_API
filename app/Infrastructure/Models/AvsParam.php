<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class AvsParam extends Model
{
    protected $fillable = ['taux_avs', 'taux_ac', 'franchise_avs', 'franchise_imposition', 'compte_id', 'ecriture_categorie_id'];
    protected $casts = [
        'taux_avs' => 'decimal:5', 'taux_ac' => 'decimal:5', 'franchise_avs' => 'decimal:2',
        'franchise_imposition' => 'decimal:2', 'compte_id' => 'integer', 'ecriture-categorie_id' => 'integer'
    ];
}
