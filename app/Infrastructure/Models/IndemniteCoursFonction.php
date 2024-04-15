<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteCoursFonction extends Model
{
    protected $fillable = [
        'type',
        'tarif',
        'compte_id',
        'fonction_id',
        'type_unite_id',
    ];
    protected function casts(): array
    {
        return  [
            'type' => 'integer',
            'tarif' => 'decimal:2',
            'compte_id' => 'integer',
            'fonction_id' => 'integer',
            'type_unite_id' => 'integer'
        ];
    }
}
