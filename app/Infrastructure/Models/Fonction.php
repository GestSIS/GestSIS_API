<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Fonction extends Model
{
    protected $fillable = ['nom', 'abreviation', 'tri', 'cumulable', 'actif'];
    protected function casts(): array
    {
        return  [
            'id' => 'integer', 'tri' => 'integer', 'cumulable' => 'boolean', 'actif' => 'boolean'
        ];
    }
}
