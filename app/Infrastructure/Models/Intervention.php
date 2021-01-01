<?php

namespace App\Infrastructure\Models;

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
        'degre',
        'sauve_personne',
        'sauve_animaux',
        'description',
        'proprietaire',
        'responsable',
        'stat_nb',
        'statut',
        'date_imputation',

        'localite_id',
        'intervention_traitement_id',
        'stat_federal_id',
        'sapeur_id',
        'type_intervention_id',
    ];

    public function localite()
    {
        return $this->belongsTo('App\Infrastructure\Models\Localite');
    }

    public function typeIntervention()
    {
        return $this->belongsTo('App\Infrastructure\Models\TypeIntervention');
    }

    /**
     * The sapeur that belong to the sapeur.
     */
    public function presences()
    {
        return $this->hasMany('App\Infrastructure\Models\InterventionSapeur');
    }

    /**
     * The sapeur that belong to the sapeur.
     */
    public function ecritures()
    {
        return $this->hasMany('App\Infrastructure\Models\Ecritures');
    }

    /**
     * The groupe that belong to the sapeur.
     */
    public function groupes()
    {
        return $this->hasMany('App\Infrastructure\Models\InterventionGroupe');
    }

    /**
     * The materiel that belong to the sapeur.
     */
    public function materiels()
    {
        return $this->hasMany('App\Infrastructure\Models\InterventionMateriel');
    }

    /**
     * The vehicule that belong to the sapeur.
     */
    public function vehicules()
    {
        return $this->hasMany('App\Infrastructure\Models\InterventionVehicule');
    }

    /**
     * The quittance that belong to the sapeur.
     */
    public function quittances()
    {
        return $this->hasMany('App\Infrastructure\Models\Quittance');
    }

    /**
     * The mission that belong to the sapeur.
     */
    public function missions()
    {
        return $this->hasMany('App\Infrastructure\Models\Mission');
    }

    /**
     * The appel that belong to the sapeur.
     */
    public function appels()
    {
        return $this->hasMany('App\Infrastructure\Models\Appel');
    }

    /**
     * The phase that belong to the sapeur.
     */
    public function phases()
    {
        return $this->hasMany('App\Infrastructure\Models\Phase');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function exerciceComptable()
    {
        return $this->belongsTo('App\Infrastructure\Models\ExerciceComptable');
    }
}
