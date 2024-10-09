<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Hangar extends Model
{
    protected $fillable = [
        'rue',
        'no_rue',
        'localite_id'
    ];
    protected function casts(): array
    {
        return  [
            'localite_id' => 'integer'
        ];
    }
}
