<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class FraisAnnuel extends Model
{
    protected $fillable = ['frais_annuel_type_id', 'type_unite_id',  'montant', 'quantite', 'fonction_id'];
    protected $casts = [
        'frais_annuel_type_id' => 'integer', 'type_unite_id' => 'integer', 'montant' => 'decimal:2', 'quantite' => 'decimal:2',
        'fonction_id' => 'integer'
    ];
}
