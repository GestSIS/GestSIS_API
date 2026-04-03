<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravailType extends Model
{
    use HasFactory;

    protected $fillable = ['designation', 'actif', 'ecriture_categorie_id', 'type_unite_id'];
    protected function casts(): array
    {
        return ['actif' => 'boolean', 'ecriture_categorie_id' => 'integer', 'type_unite_id' => 'integer'];
    }

    public function fonctions()
    {
        return $this->hasMany(TravailTypeFonction::class, 'travail_type_id');
    }
}
