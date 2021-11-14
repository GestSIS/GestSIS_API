<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteAnnuelType extends Model
{
    protected $fillable = ['compte_id', 'ecriture_categorie_id', 'designation'];

    public function indemniteAnnuels()
    {
        return $this->hasMany(IndemniteAnnuel::class);
    }
}
