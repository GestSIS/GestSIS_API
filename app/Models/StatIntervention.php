<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatIntervention extends Model
{
    protected $fillable = ['designation', 'tri'];
    protected function casts(): array
    {
        return ['tri' => 'integer'];
    }
}
