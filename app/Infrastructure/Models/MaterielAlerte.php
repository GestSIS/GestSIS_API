<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielAlerte extends Model
{
    use HasFactory;

    protected $fillable = ['titre', 'description', 'materiel_nominal_id', 'statut', 'remarque'];
    protected function casts(): array
    {
        return  ['materiel_nominal_id' => 'integer', 'statut' => 'integer'];
    }
}
