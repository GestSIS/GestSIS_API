<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ControleMedicalType extends Model
{
    protected $fillable = ['designation', 'validity_duration', 'expirable', 'tri'];
}
