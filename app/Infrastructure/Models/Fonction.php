<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Fonction extends Model
{
    protected $fillable = ['nom', 'abreviation', 'tri', 'cumulable'];
}
