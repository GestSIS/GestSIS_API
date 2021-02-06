<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class AvsParam extends Model
{
    protected $fillable = ['taux_avs', 'taux_ac','franchise', 'compte_id'];
}
