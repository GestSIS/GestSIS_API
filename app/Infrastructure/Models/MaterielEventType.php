<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielEventType extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'description', 'validable'];
    protected $casts = ['validable' => 'boolean'];
}
