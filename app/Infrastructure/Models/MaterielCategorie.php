<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielCategorie extends Model
{
    use HasFactory;

    protected $fillable = ['designation', 'pere_id'];

    protected $cast = ['pere_id' => 'integer'];
}
