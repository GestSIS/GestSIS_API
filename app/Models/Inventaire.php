<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'designation',
        'remarque',
        'emplacement_id',
        'responsable',
    ];

    protected function casts(): array
    {
        return [
            'emplacement_id' => 'integer',
        ];
    }
}
