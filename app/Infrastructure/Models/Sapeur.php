<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sapeur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'prenom', 'suffixe', 'rue', 'no_rue', 'date_naissance', 'no_avs', 'profession', 'employeur',
        'lieu_de_travail', 'email', 'actif', 'iban', 'iban_statut', 'remarque', 'porteur', 'localite_id',
        'civilite_id', 'cotisation_avs'
    ];
    protected $casts = [
        'actif' => 'integer', 'iban_status' => 'integer', 'actif' => 'integer', 'cotisation_avs' => 'integer',
        'localite_id' => 'integer', 'civilite_id' => 'integer', 'porteur' => 'integer', 'fonction_id' => 'integer',
        'grade_id' => 'integer'
    ];

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
     * Les cours du sapeurs
     */
    public function cours()
    {
        return $this->hasMany('App\Infrastructure\Models\CoursSapeur');
    }

    /**
     * Les grades du sapeur
     */
    public function grades()
    {
        return $this->hasMany('App\Infrastructure\Models\GradeSapeur');
    }

    /**
     * Les grades du sapeur
     */
    public function exercices()
    {
        return $this->hasMany('App\Infrastructure\Models\ExerciceSapeur');
    }

    /**
     * Le grade principal du sapeur
     */
    public function grade()
    {
        return $this->belongsTo('App\Infrastructure\Models\Grade');
    }

    /**
     * Les fonctions du sapeur
     */
    public function fonctions()
    {
        return $this->hasMany('App\Infrastructure\Models\FonctionSapeur');
    }

    /**
     * La fonction principale du sapeur
     */
    public function fonction()
    {
        return $this->belongsTo('App\Infrastructure\Models\Fonction');
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

    /**
     * The civility of the sapeur
     */
    public function civilite()
    {
        return $this->belongsTo('App\Infrastructure\Models\Civilite');
    }
}
