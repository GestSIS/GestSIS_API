<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteInterventionFonction extends Model
{
    protected $fillable = ['solde', 'fonction_id'];
    protected $casts = [
        'solde' => 'decimal:2', 'fonction_id' => 'integer'
    ];
}
