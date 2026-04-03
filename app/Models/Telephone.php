<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telephone extends Model
{
    protected $fillable = ['tri', 'nom', 'numero'];
    protected function casts(): array
    {
        return ['tri' => 'integer'];
    }
}
