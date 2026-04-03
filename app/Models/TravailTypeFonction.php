<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravailTypeFonction extends Model
{
    protected $fillable = [
        'type',
        'tarif',
        'compte_id',
        'fonction_id',
    ];
    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'tarif' => 'decimal:2',
            'compte_id' => 'integer',
            'fonction_id' => 'integer',
        ];
    }
}
