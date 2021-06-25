<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class AvsParam extends Model
{
    protected $fillable = ['taux_avs', 'taux_ac','franchise_avs', 'franchise_imposition', 'compte_id', 'ecriture_categorie_id'];
}
