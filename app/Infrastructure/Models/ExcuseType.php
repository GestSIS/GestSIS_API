<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ExcuseType extends Model
{
    protected $fillable = ['designation', 'abreviation', 'amende', 'status', 'tri'];
    protected $casts = [
        'status' => 'integer', 'tri' => 'integer', 'amende' => 'boolean'
    ];
}
