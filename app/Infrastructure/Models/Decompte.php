<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Decompte extends Model
{
    public function exerciceCompatble()
    {
        return $this->belongsTo('App\Infrastructure\Models\ExerciceComptable');
    }

    public function paiements()
    {
        return $this->hasMany('App\Infrastructure\Models\Paiement');
    }
}
