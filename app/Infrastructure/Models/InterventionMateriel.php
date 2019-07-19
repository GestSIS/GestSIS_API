<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionMateriel extends Model
{
    protected $table = 'intervention_materiel';
    protected $fillable = ['quantite'];
}
