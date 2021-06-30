<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteInterventionType extends Model
{
    protected $fillable = [
        'designation',
        'solde',
        'solde_min',
        'solde_min_pour',
        'taux_weekend',
        'taux_nuit',
        'debut',
        'fin',
        'compte_id',
        'phase_id',
        'type_unite_id',
        'ecriture_categorie_id',
        'par_fonction',
    ];

    public function fonctions()
    {
        return $this->hasMany('App\Infrastructure\Models\IndemniteInterventionFonction', 'indemnite_int_id');
    }
}
