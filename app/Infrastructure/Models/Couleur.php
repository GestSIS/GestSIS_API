<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Couleur extends Model
{
    protected $fillable = ['nom', 'texte', 'fond'];
}
