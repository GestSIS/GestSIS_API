<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Travail extends Model
{
    use HasFactory;
    protected $table = 'travaux';

    protected $fillable = ['designation', 'date', 'date_demande', 'statut', 'justification', 'sapeur_id', 'auteur_id', 'travail_type_id', 'quantite', 'exercice_comptable_id'];
    protected function casts(): array
    {
        return  ['statut' => 'integer', 'sapeur_id' => 'integer', 'auteur_id' => 'integer', 'travail_type_id' => 'integer', 'quantite' => 'decimal:2', 'exercice_comptable_id' => 'integer'];
    }

    public function ecritures()
    {
        return $this->hasMany(Ecriture::class, 'travail_id');
    }
}
