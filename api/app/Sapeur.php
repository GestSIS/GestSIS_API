<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sapeur extends Model
{
    //TODO: Code métier

    /**
     * The cours that belong to the user.
     */
    public function cours()
    {
        return $this->belongsToMany('App\Cours');
    }

    public function localite()
    {
        return $this->belongsTo('App\Localite');
    }
}
