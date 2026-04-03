<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielTypeBatterie extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'batterie_type_id'
    ];
    protected function casts(): array
    {
        return [
            'nombre' => 'integer',
            'batterie_type_id' => 'integer'
        ];
    }
}
