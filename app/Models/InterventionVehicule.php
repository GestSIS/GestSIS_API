<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionVehicule extends Model
{
    protected $table = 'intervention_vehicule';
    protected $fillable = ['intervention_id', 'vehicule_id'];
    protected function casts(): array
    {
        return [
            'intervention_id' => 'integer',
            'vehicule_id' => 'integer'
        ];
    }
}
