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
        'par_fonction',
        'ecriture_categorie_id'
    ];
    protected $casts = [
        'solde' => 'decimal:2', 'indemnite' => 'decimal:2', 'solde_min' => 'decimal:2', 'solde_min_pour' => 'decimal:2',
        'type_unite_id' => 'integer', 'compte_id' => 'integer', 'par_fonction' => 'boolean', 'ecriture_categorie_id' => 'integer'
    ];

    public function fonctions()
    {
        return $this->hasMany(IndemniteExerciceFonction::class, 'indemnite_exe_id');
    }
}
