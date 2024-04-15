<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionSapeur extends Model
{
    protected $table = 'intervention_sapeur';
    protected $fillable = ['piquet', 'debut', 'fin'];
    protected function casts(): array
    {
        return  [
            'piquet' => 'boolean', 'intervention_id' => 'integer', 'sapeur_id' => 'integer'
        ];
    }
}
