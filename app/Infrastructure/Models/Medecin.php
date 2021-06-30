<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medecin extends Model
{
    protected $fillable = ['designation', 'adresse', 'actif', 'localite_id'];
    use HasFactory;

    /**
     * Le sapeur
     */
    public function localite()
    {
        return $this->belongsTo('App\Infrastructure\Models\Localite');
    }
}
