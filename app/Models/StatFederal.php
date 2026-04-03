<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatFederal extends Model
{
    protected $fillable = ['designation', 'statut', 'tri'];
    protected function casts(): array
    {
        return ['statut' => 'integer', 'tri' => 'integer'];
    }
}
