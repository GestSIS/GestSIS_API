<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicule extends Model
{
    protected $fillable = ['designation', 'status', 'tri', 'forfait', 'unite', 'type_unite_id'];
}
