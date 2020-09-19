<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sapeur extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'prenom', 'suffixe', 'rue', 'no_rue', 'date_naissance', 'no_avs', 'profession', 'employeur',
                 'lieu_de_travail', 'email', 'actif', 'iban', 'iban_statut', 'remarque','porteur', 'localite_id', 'civilite_id'];

    protected $attributes = [
        'suffixe' => '',
        'remarque' => '',
        'profession' => '',
        'employeur' => '',
        'lieu_de_travail' => '',
        'email' => '',
        'iban' => ''
    ];
    /**
     * The cours that belong to the sapeur.
     */
    public function cours()
    {
        return $this->hasMany('App\Infrastructure\Models\CoursSapeur');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function grades()
    {
        return $this->hasMany('App\Infrastructure\Models\GradeSapeur');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function fonctions()
    {
        return $this->hasMany('App\Infrastructure\Models\FonctionSapeur');
    }

    /**
     * The groupes that belong to the sapeur.
     */
    public function groupes()
    {
        return $this->hasMany('App\Infrastructure\Models\GroupeSapeur');
    }

    /**
     * The Permis that belong to the sapeur.
     */
    public function permis()
    {
        return $this->hasMany('App\Infrastructure\Models\Permis');
    }

    /**
     * The Telephones that belong to the sapeur.
     */
    public function telephones()
    {
        return $this->hasMany('App\Infrastructure\Models\SapeurTelephone');
    }

    /**
     * The Mutations that belong to the sapeur.
     */
    public function mutations()
    {
        return $this->hasMany('App\Infrastructure\Models\Mutation');
    }

    /**
     * The localite where the sapeur lives
     */
    public function localite()
    {
        return $this->belongsTo('App\Infrastructure\Models\Localite');
    }
}
