<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class FraisAnnuelType extends Model
{
    protected $fillable = ['compte_id', 'ecriture_categorie_id', 'designation'];

    public function fraisAnnuels()
    {
        return $this->hasMany(FraisAnnuel::class);
    }
}
