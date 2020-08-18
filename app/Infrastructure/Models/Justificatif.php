<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Justificatif extends Model
{
    public function controleMedical()
    {
        return $this->belongsTo('App\Infrastructure\Models\ControleMedical');
    }
}
