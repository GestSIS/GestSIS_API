<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    /**
     * The sapeur that belong to the sapeur.
     */
    public function sapeurs()
    {
        return $this->hasMany('App\Infrastructure\Models\GroupeSapeur');
    }
}
