<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medecin extends Model
{
    
    /**
     * Le sapeur
     */
    public function localite()
    {
        return $this->belongsTo('App\Infrastructure\Models\Localite');
    }
}
