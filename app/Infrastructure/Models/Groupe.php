<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    protected $fillable = ['type', 'no', 'designation', 'tri', 'pere_id'];


    /**
     * The sapeur that belong to the sapeur.
     */
    public function sapeurIds()
    {
        return $this->hasMany(GroupeSapeur::class);
    }

    /**
     * The sapeur that belong to the sapeur.
     */
    public function sapeurs()
    {
        return $this->belongsToMany(Sapeur::class);
    }
}
