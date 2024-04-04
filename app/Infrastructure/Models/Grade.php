<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['designation', 'abreviation', 'groupe', 'tri'];
    protected $casts = [
        'tri' => 'integer', 'groupe' => 'integer',
    ];
}
