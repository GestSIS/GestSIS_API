<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class GroupeIntervention extends Model
{
    protected $table = 'groupe_intervention';
    protected $fillable = ['no', 'designation', 'intervention_id'];
    protected function casts(): array
    {
        return  [
            'no' => 'integer', 'intervention_id' => 'integer',
        ];
    }
}
