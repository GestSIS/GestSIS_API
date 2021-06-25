<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ControleMedicalType extends Model
{
    protected $fillable = ['designation', 'duree_validite', 'expirable', 'tri'];
}
