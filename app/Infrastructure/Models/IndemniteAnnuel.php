<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteAnnuel extends Model
{
    protected $fillable = ['indemnite_annuel_type_id', 'type_unite_id', 'montant', 'quantite', 'fonction_id'];
    protected $casts = [
        'fonction_id' => 'integer', 'indemnite_annuel_type_id' => 'integer', 'type_unite_id' => 'integer', 'montant' => 'decimal:2',
        'quantite' => 'decimal:2'
    ];
}
