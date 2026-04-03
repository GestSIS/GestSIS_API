<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciceComptable extends Model
{
    protected $fillable = ['annee', 'designation', 'debut', 'fin', 'boucle'];
    protected function casts(): array
    {
        return [
            'annee' => 'integer',
            'boucle' => 'integer'
        ];
    }
}
