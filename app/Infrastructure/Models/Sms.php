<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{

    protected $table = 'sms';

    protected $fillable = [
        'message', 'date_envoie', 'numeros', 'exercice_id'
    ];
    protected function casts(): array
    {
        return  [
            'date_envoie' => 'datetime', 'exercice_id' => 'integer'
        ];
    }
}
