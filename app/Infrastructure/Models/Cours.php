<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    protected $fillable = ['precedent_id', 'grade_id', 'fonction_id', 'abreviation', 'designation', 'tri', 'validite_debut', 'validite_fin'];
    protected $casts = [
        'precedent_id' => 'integer', 'grade_id' => 'integer', 'fonction_id' => 'integer', 'tri' => 'integer',
    ];
}
