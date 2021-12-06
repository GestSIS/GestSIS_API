<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class FraisAnnuelType extends Model
{
    protected $fillable = ['compte_id', 'ecriture_categorie_id', 'designation', 'cumulable'];
    protected $casts = [
        'compte_id' => 'integer', 'ecriture_categorie_id' => 'integer', 'cumulable' => 'boolean'
    ];

    public function fraisAnnuels()
    {
        return $this->hasMany(FraisAnnuel::class);
    }
}
