<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsenceParam extends Model
{
    protected $fillable = ['actif'];
    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }
}
