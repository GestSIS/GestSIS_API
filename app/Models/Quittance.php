<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quittance extends Model
{
    protected $fillable = ['sapeur_id', 'intervention_id'];

    protected function casts(): array
    {
        return [
            'sapeur_id' => 'integer',
            'intervention_id' => 'integer'
        ];
    }
}
