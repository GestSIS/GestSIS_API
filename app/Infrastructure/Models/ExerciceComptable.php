<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciceComptable extends Model
{
    protected $fillable = ['annee', 'designation', 'debut', 'fin', 'boucle'];
    protected $casts = [
        'annee' => 'integer', 'boucle' => 'integer'
    ];
}
