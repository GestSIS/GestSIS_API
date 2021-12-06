<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Localite extends Model
{
    //
    protected $casts = [
        'commune_id' => 'integer'
    ];
}
