<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    protected $fillable = ['type', 'no', 'designation', 'tri', 'pere_id'];
    protected $casts = [
        'no' => 'integer', 'tri' => 'integer', 'pere_id' => 'integer'
    ];


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
