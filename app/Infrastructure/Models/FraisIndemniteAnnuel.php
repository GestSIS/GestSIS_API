<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class FraisIndemniteAnnuel extends Model
{
    protected $fillable = ['frais_indemnite_annuel_type_id', 'type_unite_id', 'montant', 'quantite', 'fonction_id'];
    protected function casts(): array
    {
        return  [
            'fonction_id' => 'integer', 'frais_indemnite_annuel_type_id' => 'integer', 'type_unite_id' => 'integer', 'montant' => 'decimal:2',
            'quantite' => 'decimal:2'
        ];
    }
}
