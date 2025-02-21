<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielType extends Model
{
    use HasFactory;

    protected $fillable = ['designation', 'materiel_categorie_id'];
    protected function casts(): array
    {
        return [
            'materiel_categorie_id' => 'integer',
            'fonction_id' => 'integer',
            'est_numerote' => 'boolean',
            'est_attribuable' => 'boolean',
            'est_taillee' => 'boolean'
        ];
    }
}
