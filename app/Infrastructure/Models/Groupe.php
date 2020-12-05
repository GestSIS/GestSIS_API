<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    protected $fillable = ['pere_id', 'type', 'no', 'designation', 'info', 'tri'];

    /**
     * The sapeur that belong to the sapeur.
     */
    public function sapeurs()
    {
        return $this->hasMany('App\Infrastructure\Models\GroupeSapeur');
    }
}
