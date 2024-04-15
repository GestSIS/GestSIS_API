<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielPersonnel extends Model
{
    use HasFactory;

    protected $fillable = ['taille', 'remarque', 'attribution', 'retour', 'sapeur_id'];
    protected function casts(): array
    {
        return  ['sapeur_id' => 'integer', 'materiel_type_id' => 'integer', 'materiel_id' => 'integer'];
    }

    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }

    public function materiel()
    {
        return $this->morphTo();
    }
}
