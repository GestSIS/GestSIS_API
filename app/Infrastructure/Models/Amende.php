<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Amende extends Model
{
    protected $fillable = ['ordre', 'montant', 'compte_id', 'ecriture_categorie_id'];
    protected $casts = [
        'ordre' => 'integer', 'montant' => 'float', 'compte_id' => 'integer', 'ecriture_categorie_id' => 'integer'
    ];
}
