<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sapeur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'suffixe',
        'rue',
        'no_rue',
        'date_naissance',
        'no_avs',
        'profession',
        'employeur',
        'lieu_de_travail',
        'email',
        'actif',
        'iban',
        'iban_statut',
        'remarque',
        'porteur',
        'localite_id',
        'civilite_id',
        'cotisation_avs',
        'annee_incorporation'
    ];
    protected function casts(): array
    {
        return [
            'actif' => 'integer',
            'iban_statut' => 'integer',
            'cotisation_avs' => 'integer',
            'localite_id' => 'integer',
            'civilite_id' => 'integer',
            'porteur' => 'integer',
            'fonction_id' => 'integer',
            'grade_id' => 'integer',
            'type' => 'integer'
        ];
    }

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
        return $this->hasMany(CoursSapeur::class);
    }

    /**
     * Les grades du sapeur
     */
    public function grades()
    {
        return $this->hasMany(GradeSapeur::class);
    }

    /**
     * Les grades du sapeur
     */
    public function exercices()
    {
        return $this->hasMany(ExerciceSapeur::class);
    }

    /**
     * Le grade principal du sapeur
     */
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Les fonctions du sapeur
     */
    public function fonctions()
    {
        return $this->hasMany(FonctionSapeur::class);
    }

    /**
     * La fonction principale du sapeur
     */
    public function fonction()
    {
        return $this->belongsTo(Fonction::class);
    }

    /**
     * The groupes that belong to the sapeur.
     */
    public function groupes()
    {
        return $this->hasMany(GroupeSapeur::class);
    }

    /**
     * The Permis that belong to the sapeur.
     */
    public function permis()
    {
        return $this->hasMany(Permis::class);
    }

    /**
     * The Telephones that belong to the sapeur.
     */
    public function telephones()
    {
        return $this->hasMany(SapeurTelephone::class);
    }

    /**
     * The Mutations that belong to the sapeur.
     */
    public function mutations()
    {
        return $this->hasMany(Mutation::class);
    }

    /**
     * The localite where the sapeur lives
     */
    public function localite()
    {
        return $this->belongsTo(Localite::class);
    }

    /**
     * The civility of the sapeur
     */
    public function civilite()
    {
        return $this->belongsTo(Civilite::class);
    }

    /**
     * Les grades du sapeur
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
