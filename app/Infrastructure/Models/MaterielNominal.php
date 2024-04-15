<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielNominal extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'numero', 'achat'];
    protected function casts(): array
    {
        return  ['achat' => 'datetime'];
    }

    public function materiel()
    {
        return $this->morphOne(MaterielPersonnel::class, 'materiel');
    }

    public function events()
    {
        return $this->hasMany(MaterielEvent::class, 'materiel_nominal_id');
    }
}
