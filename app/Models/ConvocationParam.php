<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConvocationParam extends Model
{
    protected $fillable = ['titre', 'texte_debut', 'texte_fin', 'texte_pour_info', 'affichage_duree', 'affichage_pour_info'];
    protected function casts(): array
    {
        return ['affichage_pour_info' => 'boolean', 'affichage_duree' => 'boolean'];
    }
}
