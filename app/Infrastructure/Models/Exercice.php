<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercice extends Model
{
    protected $fillable = ['date', 'heure', 'lieu', 'designation', 'communications', 'duree', 'statut', 'exercice_categorie_id', 'localite_id'];
    protected $casts = [
        'duree' => 'integer', 'statut' => 'integer', 'localite_id' => 'integer', 'exercice_categorie_id' => 'integer'
    ];

    use HasFactory;

    //Statut:
    // 0 -> Annulé
    // 1 -> A saisir
    // 2 -> En attente de validation
    // 3 -> Disponible pour imputation
    // 4 -> Imputée

    public function ecritures()
    {
        return $this->hasMany('App\Infrastructure\Models\Ecriture');
    }

    public function localite()
    {
        return $this->belongsTo('App\Infrastructure\Models\Localite');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function sapeurs()
    {
        return $this->hasMany('App\Infrastructure\Models\ExerciceSapeur');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function categorie()
    {
        return $this->belongsTo('App\Infrastructure\Models\ExerciceCategorie');
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function exerciceComptable()
    {
        return $this->belongsTo('App\Infrastructure\Models\ExerciceComptable');
    }
}
