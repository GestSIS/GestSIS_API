<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercice extends Model
{
    protected $fillable = ['date', 'heure', 'lieu', 'designation', 'communications', 'duree', 'statut', 'exercice_categorie_id', 'localite_id', 'statut'];

    //Statut:
    // 0 -> Annulé
    // 1 -> A saisir
    // 2 -> En attente de validation
    // 3 -> Disponible pour imputation
    // 4 -> Imputée

    /**
     * The cours that belong to the sapeur.
     */
    public function sapeurs()
    {
        return $this->hasMany('App\Models\ExerciceSapeur');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function categorie()
    {
        return $this->belongsTo('App\Models\ExerciceCategorie');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function exerciceComptable()
    {
        return $this->belongsTo('App\Models\ExerciceComptable');
    }
}
