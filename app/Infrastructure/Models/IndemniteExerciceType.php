<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteExerciceType extends Model
{
    protected $fillable = [
        'designation',
        'solde',
        'indemnite',
        'solde_min',
        'solde_min_pour',
        'type_unite_id',
        'compte_id',
        'par_fonction'
    ];

    public function fonctions()
    {
        return $this->hasMany('App\Infrastructure\Models\IndemniteExerciceFonction', 'indemnite_exe_id');
    }
}
