<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{

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

        'localite_id',
        'intervention_traitement_id',
        'stat_federal_id',
        'sapeur_id',
        'type_intervention_id',
    ];

    /**
     * The sapeur that belong to the sapeur.
     */
    public function presences()
    {
        return $this->hasMany('App\Models\InterventionSapeur');
    }

    /**
     * The groupe that belong to the sapeur.
     */
    public function groupes()
    {
        return $this->hasMany('App\Models\InterventionGroupe');
    }

    /**
     * The materiel that belong to the sapeur.
     */
    public function materiels()
    {
        return $this->hasMany('App\Models\InterventionMateriel');
    }

    /**
     * The vehicule that belong to the sapeur.
     */
    public function vehicules()
    {
        return $this->hasMany('App\Models\InterventionVehicule');
    }

    /**
     * The quittance that belong to the sapeur.
     */
    public function quittances()
    {
        return $this->hasMany('App\Models\Quittance');
    }

    /**
     * The mission that belong to the sapeur.
     */
    public function missions()
    {
        return $this->hasMany('App\Models\Mission');
    }

    /**
     * The appel that belong to the sapeur.
     */
    public function appels()
    {
        return $this->hasMany('App\Models\Appel');
    }

    /**
     * The phase that belong to the sapeur.
     */
    public function phases()
    {
        return $this->hasMany('App\Models\Phase');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function exerciceComptable()
    {
        return $this->belongsTo('App\Models\ExerciceComptable');
    }
}
