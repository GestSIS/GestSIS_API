<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeSapeur extends Model
{
    protected $table = 'grade_sapeur';
    protected $fillable = ['date', 'remarque'];
}
