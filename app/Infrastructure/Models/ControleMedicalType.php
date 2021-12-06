<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ControleMedicalType extends Model
{
    protected $fillable = ['designation', 'duree_validite', 'expirable', 'tri'];
    protected $casts = [
        'tri' => 'integer', 'duree_validite' => 'integer', 'expirable' => 'integer'
    ];
}
