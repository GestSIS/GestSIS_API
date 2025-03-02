<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'designation',
        'date',
        'remarque',
        'responsable',
        'maintenance_type_id',
    ];

    protected function casts(): array
    {
        return  [
            'maintenance_type_id' => 'integer',
        ];
    }
}
