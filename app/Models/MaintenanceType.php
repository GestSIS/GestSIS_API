<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'designation',
        'periodicite',
        'externalise',
        'nb_max',
    ];

    protected function casts(): array
    {
        return [
            'periodicite' => 'integer',
            'nb_max' => 'integer',
            'externalise' => 'boolean',
        ];
    }
}
