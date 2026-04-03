<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupeIntervention extends Model
{
    protected $table = 'groupe_intervention';
    protected $fillable = ['no', 'designation', 'intervention_id'];
    protected function casts(): array
    {
        return [
            'intervention_id' => 'integer',
        ];
    }
}
