<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteExerciceFonction extends Model
{
    protected $fillable = ['solde', 'indemnite', 'fonction_id'];
    protected $casts = [
        'solde' => 'decimal:2', 'indemnite' => 'decimal:2', 'fonction_id' => 'integer'
    ];
}
