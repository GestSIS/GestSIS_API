<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Quittance extends Model
{
    protected $casts = [
        'sapeur_id' => 'integer', 'intervention_id' => 'integer'
    ];
}
