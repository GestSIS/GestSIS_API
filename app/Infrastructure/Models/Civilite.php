<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Civilite extends Model
{
    protected $fillable = ['designation', 'forme_politesse'];
}
