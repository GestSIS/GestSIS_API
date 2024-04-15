<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteExerciceFonction extends Model
{
    protected $fillable = [
        'tarif',
        'fonction_id',
        'compte_id',
        'type',
    ];
    protected function casts(): array
    {
        return  [
            'tarif' => 'decimal:2',
            'fonction_id' => 'integer',
            'compte_id' => 'integer',
            'type' => 'integer'
        ];
    }
}
