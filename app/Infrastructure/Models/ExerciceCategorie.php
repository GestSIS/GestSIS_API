<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciceCategorie extends Model
{
    protected $fillable = ['designation', 'amendable', 'duree_base', 'status', 'tri'];
    protected $casts = [
        'amendable' => 'boolean', 'duree_base' => 'integer', 'status' => 'integer', 'tri' => 'integer',
    ];
}
