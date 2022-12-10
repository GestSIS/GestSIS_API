<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravailType extends Model
{
    protected $fillable = ['designation', 'tarif', 'type', 'type_unite_id', 'actif', 'compte_id', 'ecriture_categorie_id'];
    protected $casts = [
        'tarif' => 'decimal', 'type' => 'integer', 'type_unite_id' => 'integer', 'actif' => 'boolean', 'compte_id' => 'integer', 'ecriture_categorie_id' => 'integer'
    ];

    use HasFactory;
}
