<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercice extends Model
{
    //

    /**
     * The cours that belong to the sapeur.
     */
    public function sapeurs()
    {
        return $this->hasMany('App\Models\ExerciceSapeur');
    }
}
