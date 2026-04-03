<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amende extends Model
{
    protected $fillable = ['ordre', 'montant', 'compte_id', 'ecriture_categorie_id'];
    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'montant' => 'decimal:2',
            'compte_id' => 'integer',
            'ecriture_categorie_id' => 'integer'
        ];
    }
}
