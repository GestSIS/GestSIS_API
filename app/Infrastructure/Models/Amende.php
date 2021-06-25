<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Amende extends Model
{
    protected $fillable = ['ordre', 'montant', 'compte_id', 'ecriture_categorie_id'];
}
