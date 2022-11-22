<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    protected $fillable = ['incorporation', 'sortie', 'motif', 'localite_id'];
    protected $casts = [
        'localite_id' => 'integer', 'sapeur_id' => 'integer'
    ];
}
