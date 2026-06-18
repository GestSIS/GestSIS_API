<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterielTypeTuyau extends Model
{
    protected $table = 'materiel_type_tuyaux';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tuyau_diametre_id',
        'longeur',
        'separement',
    ];
    protected function casts(): array
    {
        return [
            'tuyau_diametre_id' => 'integer',
            'longeur' => 'integer',
            'separement' => 'boolean',
        ];
    }
}
