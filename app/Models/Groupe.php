<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    /**
     * The sapeur that belong to the sapeur.
     */
    public function sapeurs()
    {
        return $this->hasMany('App\Models\GroupeSapeur');
    }
}
