<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_debut',
        'heure_debut',
        'date_fin',
        'heure_fin',
        'lieu',
        'objet',
        'rapport_police',
        'agent',
        'degre',
        'sauve_personne',
        'sauve_animaux',
        'description',
        'proprietaire',
        'responsable',
        'stat_nb',
        'wgs84',
        'statut',
        'date_imputation',

        'localite_id',
        'intervention_traitement_id',
        'stat_federal_id',
        'sapeur_id',
        'type_intervention_id',
    ];
    protected function casts(): array
    {
        return [
            'rapport_police' => 'boolean',
            'degre' => 'integer',
            'sauve_personne' => 'integer',
            'sauve_animaux' => 'integer',
            'stat_nb' => 'integer',
            'statut' => 'integer',
            'localite_id' => 'integer',
            'intervention_traitement_id' => 'integer',
            'stat_federal_id' => 'integer',
            'sapeur_id' => 'integer',
            'type_intervention_id' => 'integer'
        ];
    }

    public function localite()
    {
        return $this->belongsTo(Localite::class);
    }

    public function statFederal()
    {
        return $this->belongsTo(StatFederal::class);
    }

    public function traitement()
    {
        return $this->belongsTo(InterventionTraitement::class, 'intervention_traitement_id');
    }

    public function chefIntervention()
    {
        return $this->belongsTo(Sapeur::class, 'sapeur_id');
    }

    public function typeIntervention()
    {
        return $this->belongsTo(TypeIntervention::class);
    }

    /**
     * Les présences
     */
    public function presences()
    {
        return $this->hasMany(InterventionSapeur::class);
    }

    /**
     * Les écritures lié à l'intervention
     */
    public function ecritures()
    {
        return $this->hasMany(Ecriture::class);
    }

    /**
     * Les groupes alarmés
     */
    public function groupes()
    {
        return $this->hasMany(GroupeIntervention::class);
    }

    /**
     * Le matériel utilisé
     */
    public function materiels()
    {
        return $this->hasMany(InterventionMateriel::class);
    }

    /**
     * Les véhicules engagés
     */
    public function vehicules()
    {
        return $this->hasMany(InterventionVehicule::class);
    }

    /**
     * Les véhicules engagés
     */
    public function vehiculesInter()
    {
        return $this->belongsToMany(Vehicule::class);
    }

    /**
     * les quittances
     */
    public function quittances()
    {
        return $this->hasMany(Quittance::class);
    }

    /**
     * The mission that belong to the sapeur.
     */
    public function sapeurs()
    {
        return $this->hasMany(InterventionSapeur::class);
    }

    /**
     * The mission that belong to the sapeur.
     */
    public function missions()
    {
        return $this->hasMany(Mission::class);
    }

    /**
     * The appel that belong to the sapeur.
     */
    public function appels()
    {
        return $this->hasMany(Appel::class);
    }

    /**
     * The phase that belong to the sapeur.
     */
    public function phases()
    {
        return $this->hasMany(Phase::class);
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function exerciceComptable()
    {
        return $this->belongsTo(ExerciceComptable::class);
    }
}
