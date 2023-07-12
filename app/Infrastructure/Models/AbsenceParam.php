<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class AbsenceParam extends Model
{
    protected $fillable = ['actif'];
    protected $casts = ['actif' => 'boolean'];
}
