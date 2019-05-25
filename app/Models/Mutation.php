<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    protected $fillable = ['incorporation', 'sortie', 'motif', 'localite_id'];
}
