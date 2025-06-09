<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Emplacement extends Model
{
    protected $fillable = [
        'designation',
        'remarque',
        'tri',
        'est_etiquete',
        'impression_inventaire',
        'couleur_id',
        'parent_id',
        'statut'
    ];
    protected function casts(): array
    {
        return [
            'statut' => 'boolean',
            'tri' => 'integer',
            'impression_inventaire' => 'date',
            'est_etiquete' => 'boolean',
            'couleur_id' => 'integer',
            'parent_id' => 'integer'
        ];
    }
}
