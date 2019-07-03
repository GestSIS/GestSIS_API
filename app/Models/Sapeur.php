<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sapeur extends Model
{
    protected $fillable = ['nom', 'prenom', 'suffixe', 'rue', 'no_rue', 'date_naissance', 'no_avs', 'profession', 'employeur',
                 'lieu_de_travail', 'email', 'actif', 'iban', 'iban_statut', 'remarque','porteur', 'localite_id', 'civilite_id'];

    /**
     * The cours that belong to the sapeur.
     */
    public function cours()
    {
        return $this->hasMany('App\Models\CoursSapeur');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function grades()
    {
        return $this->hasMany('App\Models\GradeSapeur');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function fonctions()
    {
        return $this->hasMany('App\Models\FonctionSapeur');
    }

    /**
     * The groupes that belong to the sapeur.
     */
    public function groupes()
    {
        return $this->hasMany('App\Models\GroupeSapeur');
    }

    /**
     * The Permis that belong to the sapeur.
     */
    public function permis()
    {
        return $this->hasMany('App\Models\Permis');
    }

    /**
     * The Telephones that belong to the sapeur.
     */
    public function telephones()
    {
        return $this->hasMany('App\Models\SapeurTelephone');
    }

    /**
     * The Mutations that belong to the sapeur.
     */
    public function mutations()
    {
        return $this->hasMany('App\Models\Mutation');
    }

    /**
     * The localite where the sapeur lives
     */
    public function localite()
    {
        return $this->belongsTo('App\Models\Localite');
    }
}
