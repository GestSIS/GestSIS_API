<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielType extends Model
{
    use HasFactory;

    protected $fillable = ['designation', 'materiel_categorie_id'];
    protected $casts = ['materiel_categorie_id' => 'integer'];
}
