<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    protected $fillable = ['lieu', 'date'];
}
