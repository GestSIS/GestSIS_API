<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class MaterielTypeTuyau extends Model
{
    protected $table = 'materiel_type_tuyaux';

    protected $fillable = [
        'longeure',
        'tuyau_diametre_id',
        'separe',
    ];
    protected function casts(): array
    {
        return  [
            'longeure' => 'integer',
            'tuyau_diametre_id' => 'integer',
            'separe' => 'boolean',
        ];
    }
}
