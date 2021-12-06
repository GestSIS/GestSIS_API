<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class GroupeIntervention extends Model
{
    protected $table = 'groupe_intervention';
    protected $casts = [
        'no' => 'integer', 'intervention_id' => 'integer',
    ];
}
