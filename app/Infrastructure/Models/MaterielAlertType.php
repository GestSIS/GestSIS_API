<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielAlertType extends Model
{
    use HasFactory;

    protected $fillable = ['titre', 'description', 'seuil_min', 'dernier'];
    protected $casts = ['seuil_min' => 'integer', 'dernier' => 'boolean'];
}
