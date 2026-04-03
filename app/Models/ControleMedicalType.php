<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControleMedicalType extends Model
{
    protected $fillable = ['designation', 'remarque', 'duree_validite', 'expirable', 'tri'];
    protected function casts(): array
    {
        return [
            'tri' => 'integer',
            'duree_validite' => 'integer',
            'expirable' => 'boolean'
        ];
    }
}
