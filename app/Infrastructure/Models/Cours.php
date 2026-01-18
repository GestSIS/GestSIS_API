<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    use HasFactory;
    protected $fillable = ['precedent_id', 'grade_id', 'fonction_id', 'abreviation', 'designation', 'tri', 'validite_debut', 'validite_fin', 'duree'];
    protected function casts(): array
    {
        return [
            'precedent_id' => 'integer',
            'grade_id' => 'integer',
            'fonction_id' => 'integer',
            'tri' => 'integer',
            'duree' => 'decimal:2',
        ];
    }
}
