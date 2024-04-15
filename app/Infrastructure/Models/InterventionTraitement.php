<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class InterventionTraitement extends Model
{
    protected $fillable = ['tri', 'designation'];
    protected function casts(): array
    {
        return  [
            'tri' => 'integer'
        ];
    }
}
