<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteInterventionFonction extends Model
{
    protected $fillable = ['tarif', 'fonction_id'];
    protected function casts(): array
    {
        return [
            'tarif' => 'decimal:2', 'fonction_id' => 'integer'
        ];
    }
}
