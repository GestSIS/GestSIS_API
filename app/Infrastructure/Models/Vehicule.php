<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicule extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'forfait',
        'unite',
        'type_unite_id'
    ];

    protected function casts(): array
    {
        return  [
            'forfait' => 'decimal:2',
            'unite' => 'decimal:2',
            'type_unite_id' => 'integer'
        ];
    }
}
