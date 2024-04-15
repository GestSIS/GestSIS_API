<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class EcritureCategorie extends Model
{
    protected $fillable = ['designation', 'tri'];
    protected function casts(): array
    {
        return  [
            'tri' => 'integer'
        ];
    }
}
