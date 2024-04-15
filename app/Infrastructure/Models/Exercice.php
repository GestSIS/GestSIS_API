<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercice extends Model
{
    protected $fillable = ['date', 'heure', 'lieu', 'designation', 'communications', 'duree', 'statut', 'exercice_categorie_id', 'localite_id'];
    protected function casts(): array
    {
        return  [
            'duree' => 'integer', 'statut' => 'integer', 'localite_id' => 'integer', 'exercice_categorie_id' => 'integer'
        ];
    }

    use HasFactory;

    // Statut:
    // 0 -> Annulé
    // 1 -> A saisir
    // 2 -> En attente de validation
    // 3 -> Disponible pour imputation
    // 4 -> Imputée

    public function ecritures()
    {
        return $this->hasMany(Ecriture::class);
    }

    public function localite()
    {
        return $this->belongsTo(Localite::class);
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function sapeurs()
    {
        return $this->hasMany(ExerciceSapeur::class);
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function categorie()
    {
        return $this->belongsTo(ExerciceCategorie::class);
    }

    /**
     * The cours that belong to the sapeur.
     */
    public function exerciceComptable()
    {
        return $this->belongsTo(ExerciceComptable::class);
    }
}
