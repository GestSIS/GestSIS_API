<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionVehicule extends Model
{
    protected $table = 'intervention_vehicule';
    protected $casts = [
        'intervention_id' => 'integer', 'vehicule_id' => 'integer'
    ];
}
