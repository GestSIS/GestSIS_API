<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielCategorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'designation',
        'parent_id',
        'couleur_id',
        'tri'
    ];

    protected $cast = [
        'parent_id' => 'integer',
        'couleur_id' => 'integer',
        'tri' => 'integer'
    ];
}
