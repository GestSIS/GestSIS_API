<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionVehicule extends Model
{
    protected $table = 'intervention_vehicule';
    protected $fillable = ['quantite', 'forfait', 'utilisation', 'unite'];
}
