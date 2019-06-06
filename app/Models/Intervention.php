<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    protected $fillable = ['piquet', 'debut', 'fin'];

    /**
     * The cours that belong to the sapeur.
     */
    public function sapeurs()
    {
        return $this->hasMany('App\Models\InterventionSapeur');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function groupes()
    {
        return $this->hasMany('App\Models\InterventionGroupe');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function materiels()
    {
        return $this->hasMany('App\Models\InterventionMateriel');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function vehicules()
    {
        return $this->hasMany('App\Models\InterventionVehicule');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function quittances()
    {
        return $this->hasMany('App\Models\Quittance');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function missions()
    {
        return $this->hasMany('App\Models\InterventionMission');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function appels()
    {
        return $this->hasMany('App\Models\InterventionAppel');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function exerciceComptable()
    {
        return $this->belongsTo('App\Models\ExerciceComptable');
    }
}
