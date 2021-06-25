<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class TypeIntervention extends Model
{
    protected $fillable = ['designation', 'tri', 'stat_intervention_id'];
}
