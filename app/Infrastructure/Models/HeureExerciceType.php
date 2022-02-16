<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class HeureExerciceType extends Model
{
    protected $fillable = ['designation', 'montant', 'compte_id', 'ecriture_categorie_id', 'type_unite_id'];
    protected $casts = [
        'montant' => 'decimal:2', 'compte_id' => 'integer', 'ecriture_categorie_id' => 'integer', 'type_unite_id' => 'integer'
    ];
}
