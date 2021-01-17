<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciceComptable extends Model
{
    protected $fillable = ['annee', 'debut', 'fin', 'designation'];
}
