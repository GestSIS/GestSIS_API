<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ExcuseType extends Model
{
    protected $fillable = ['designation', 'abreviation', 'amende', 'statut', 'tri'];
    protected $casts = [
        'statut' => 'integer', 'tri' => 'integer', 'amende' => 'boolean'
    ];
}
