<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    protected $fillable = ['numero', 'designation', 'produit'];
    protected function casts(): array
    {
        return  [
            'produit' => 'integer'
        ];
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function ecritures()
    {
        return $this->hasMany(Ecriture::class);
    }
}
