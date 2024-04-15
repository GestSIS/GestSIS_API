<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class HeureExercice extends Model
{
    protected $fillable = ['designation', 'quantite', 'montant', 'compte_id', 'ecriture_categorie_id', 'type_unite_id', 'exercice_id', 'sapeur_id', 'heure_exercice_type_id', 'type'];
    protected function casts(): array
    {
        return  [
            'montant' => 'decimal:2', 'quantite' => 'decimal:2', 'compte_id' => 'integer', 'ecriture_categorie_id' => 'integer', 'type_unite_id' => 'integer',
            'exercice_id' => 'integer', 'sapeur_id' => 'integer', 'heure_exercice_type_id' => 'integer', 'type' => 'integer'
        ];
    }

    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }
}
