<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionMateriel extends Model
{
    protected $table = 'intervention_materiel';
    protected $fillable = ['quantite', 'forfait', 'utilisation', 'unite'];
}
