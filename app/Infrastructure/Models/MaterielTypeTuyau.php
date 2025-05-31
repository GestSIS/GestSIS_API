<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class MaterielTypeTuyau extends Model
{
    protected $table = 'materiel_type_tuyaux';

    protected $fillable = [
        'tuyau_diametre_id',
        'longeure',
        'separement',
    ];
    protected function casts(): array
    {
        return [
            'tuyau_diametre_id' => 'integer',
            'longeure' => 'integer',
            'separement' => 'boolean',
        ];
    }
}
