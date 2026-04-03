<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeIntervention extends Model
{
    protected $fillable = ['designation', 'tri', 'stat_intervention_id'];
    protected function casts(): array
    {
        return [
            'tri' => 'integer',
            'stat_intervention_id' => 'integer'
        ];
    }
}
