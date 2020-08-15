<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ControleMedical extends Model
{
    protected $table = 'controles_medicaux';

    /**
     * Le sapeur
     */
    public function sapeur()
    {
        return $this->belongsTo('App\Infrastructure\Models\Sapeur');
    }

    /**
     * La médecin
     */
    public function medecin()
    {
        return $this->belongsTo('App\Infrastructure\Models\Medecin');
    }
    
    /**
     * Le type
     */
    public function type()
    {
        return $this->belongsTo('App\Infrastructure\Models\ControleMedicalType');
    }
}
