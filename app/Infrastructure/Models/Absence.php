<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = ['debut', 'fin'];
}
