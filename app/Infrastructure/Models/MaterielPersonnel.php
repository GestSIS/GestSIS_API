<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielPersonnel extends Model
{
    use HasFactory;

    protected $fillable = ['taille', 'remarque', 'attribution', 'retour', 'sapeur_id'];
    protected $casts = ['attribution' => 'datetime', 'retour' => 'datetime', 'sapeur_id' => 'integer'];

    public function materiel()
    {
        return $this->morphTo();
    }

    public function events()
    {
        return $this->hasMany(MaterielEvent::class, 'materiel_event_id');
    }
}
