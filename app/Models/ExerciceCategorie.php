<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciceCategorie extends Model
{
    protected $fillable = ['designation', 'amendable', 'duree_base', 'statut', 'tri'];
    protected function casts(): array
    {
        return [
            'amendable' => 'boolean',
            'duree_base' => 'integer',
            'statut' => 'integer',
            'tri' => 'integer',
        ];
    }
}
