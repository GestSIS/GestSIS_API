<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielEventPourType extends Model
{
    use HasFactory;

    protected $fillable = [];
    protected $casts = ['materiel_type_id' => 'integer', 'materiel_event_type_id' => 'integer'];
}
