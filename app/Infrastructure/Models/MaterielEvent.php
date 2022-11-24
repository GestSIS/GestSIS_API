<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'remarque', 'succes'
    ];
    protected $casts = [
        'date' => 'date', 'succes' => 'boolean', 'materiel_nominal_id' => 'integer', 'materiel_event_type_id' => 'integer'
    ];
}
