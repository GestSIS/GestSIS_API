<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoursSapeur extends Model
{
    protected $fillable = ['date', 'localite_id'];
    protected $table = 'cours_sapeur';
}
