<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class CoursSapeur extends Model
{
    protected $fillable = ['date', 'localite_id'];
    protected $casts = [
        'localite_id' => 'integer'
    ];
    protected $table = 'cours_sapeur';
}
