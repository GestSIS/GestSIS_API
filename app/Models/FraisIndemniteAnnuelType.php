<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraisIndemniteAnnuelType extends Model
{
    protected $fillable = ['compte_id', 'ecriture_categorie_id', 'designation', 'cumulable', 'type'];
    protected function casts(): array
    {
        return [
            'compte_id' => 'integer',
            'ecriture_categorie_id' => 'integer',
            'cumulable' => 'boolean',
            'type' => 'integer'
        ];
    }

    public function fraisIndemniteAnnuels()
    {
        return $this->hasMany(FraisIndemniteAnnuel::class);
    }
}
