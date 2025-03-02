<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'designation',
        'periodicite',
        'externalise',
    ];

    protected function casts(): array
    {
        return  [
            'periodicite' => 'integer',
            'externalise' => 'boolean',
        ];
    }
}
