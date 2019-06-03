<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciceSapeur extends Model
{
    protected $table = 'exercice_sapeur';
    protected $fillable = ['convoque', 'present', 'amende', 'remplace', 'excuse_type_id'];
}
