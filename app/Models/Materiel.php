<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materiel extends Model
{
    protected $fillable = ['designation', 'statut', 'tri', 'forfait', 'unite', 'type_unite_id'];
    protected function casts(): array
    {
        return [
            'statut' => 'integer',
            'tri' => 'integer',
            'forfait' => 'decimal:2',
            'unite' => 'decimal:2',
            'type_unite_id' => 'integer'
        ];
    }
}
