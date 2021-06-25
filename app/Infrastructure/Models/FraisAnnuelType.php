<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class FraisAnnuelType extends Model
{
    protected $fillable = ['compte_id', 'type_unite_id', 'ecriture_categorie_id', 'montant', 'quantite', 'fonction_id', 'designation'];
}
