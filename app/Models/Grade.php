<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;
    protected $fillable = ['designation', 'abreviation', 'groupe', 'tri'];
    protected function casts(): array
    {
        return [
            'tri' => 'integer',
            'groupe' => 'integer',
        ];
    }
}
