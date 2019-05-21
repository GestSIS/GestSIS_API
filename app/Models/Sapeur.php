<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sapeur extends Model
{
    //TODO: Code métier

    protected $fillable = ['nom', 'prenom', 'suffixe', 'rue', 'no_rue', 'date_naissance', 'no_avs', 'profession', 'employeur',
                 'lieu_de_travail', 'email', 'actif', 'iban', 'iban_status', 'remarque','porteur', 'localite_id'];

    /**
     * The cours that belong to the user.
     */
    public function cours()
    {
        return $this->belongsToMany('App\Models\Cours');
    }

    /**
     * The Permis that belong to the user.
     */
    public function permis()
    {
        return $this->hasMany('App\Models\Permis');
    }

    /**
     * The Telephones that belong to the user.
     */
    public function telephones()
    {
        return $this->hasMany('App\Models\SapeursTelephone');
    }

    public function localite()
    {
        return $this->belongsTo('App\Models\Localite');
    }
}
