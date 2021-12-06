<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterventionMateriel extends Model
{
    use HasFactory;

    protected $table = 'intervention_materiel';
    protected $fillable = ['quantite'];
    protected $casts = [
        'quantite' => 'decimal:2', 'materiel_id' => 'integer', 'intervention_id' => 'integer',
    ];
}
