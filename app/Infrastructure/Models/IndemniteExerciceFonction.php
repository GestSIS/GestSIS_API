<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteExerciceFonction extends Model
{
    protected $fillable = [
        'tarif',
        'tarif_min',
        'tarif_min_pour',
        'fonction_id',
        'compte_id'
    ];
    protected $casts = [
        'tarif' => 'decimal:2', 'tarif_min' => 'decimal:2', 'tarif_min_pour' => 'decimal:2', 'fonction_id' => 'integer', 'compte_id' => 'integer'
    ];
}
