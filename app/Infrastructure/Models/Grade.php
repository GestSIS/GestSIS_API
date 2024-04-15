<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['designation', 'abreviation', 'groupe', 'tri'];
    protected function casts(): array
    {
        return  [
            'tri' => 'integer', 'groupe' => 'integer',
        ];
    }
}
