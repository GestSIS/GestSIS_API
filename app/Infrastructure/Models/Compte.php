<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    protected $fillable = ['numero', 'designation', 'actif'];

    /**
     * The cours that belong to the sapeur.
     */
    public function ecritures()
    {
        return $this->hasMany(Ecriture::class);
    }
}
