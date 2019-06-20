<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionSapeur extends Model
{
    protected $table = 'intervention_sapeur';
    protected $fillable = ['piquet', 'debut', 'fin'];
}
