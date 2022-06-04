<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class LocaliteSis extends Model
{
    //
    protected $casts = [
        'localite_id' => 'integer'
    ];
    protected $table = 'localite_sis';
}
