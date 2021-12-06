<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Phase extends Model
{
    protected $fillable = ['debut', 'phase_type_id'];
    protected $casts = [
        'phase_type_id' => 'integer', 'intervention_id' => 'integer'
    ];
}
