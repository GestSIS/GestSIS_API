<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fonction extends Model
{
    use HasFactory;
    protected $fillable = ['nom', 'abreviation', 'tri', 'cumulable', 'actif'];
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tri' => 'integer',
            'cumulable' => 'boolean',
            'actif' => 'boolean'
        ];
    }
}
