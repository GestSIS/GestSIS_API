<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class CoursSapeur extends Model
{
    protected $fillable = ['date', 'localite_id', 'duree'];
    protected $casts = [
        'localite_id' => 'integer', 'duree' => 'decimal:2'
    ];
    protected $table = 'cours_sapeur';
}
