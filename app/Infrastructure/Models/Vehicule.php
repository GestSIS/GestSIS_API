<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicule extends Model
{
    protected $fillable = ['designation', 'status', 'tri', 'forfait', 'unite', 'type_unite_id'];
    protected $casts = [
        'status' => 'integer', 'tri' => 'integer', 'forfait' => 'decimal:2', 'unite' => 'decimal:2',
        'type_unite_id' => 'integer'
    ];
}
