<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Travail extends Model
{
    use HasFactory;
    protected $table = 'travaux';

    protected $fillable = ['designation', 'date', 'date_demande', 'statut', 'justification', 'sapeur_id', 'auteur_id', 'travail_type_id', 'quantite', 'exercice_comptable_id', 'exercice_id', 'intervention_id'];
    protected function casts(): array
    {
        return ['statut' => 'integer', 'sapeur_id' => 'integer', 'auteur_id' => 'integer', 'travail_type_id' => 'integer', 'quantite' => 'decimal:2', 'exercice_comptable_id' => 'integer', 'exercice_id' => 'integer', 'intervention_id' => 'integer'];
    }

    public function travailType()
    {
        return $this->belongsTo(TravailType::class);
    }

    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }

    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }

    public function ecritures()
    {
        return $this->hasMany(Ecriture::class, 'travail_id');
    }
}
