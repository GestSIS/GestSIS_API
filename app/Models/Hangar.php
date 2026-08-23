<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hangar extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'rue',
        'no_rue',
        'localite_id'
    ];
    protected function casts(): array
    {
        return [
            'localite_id' => 'integer'
        ];
    }
}
