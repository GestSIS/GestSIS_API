<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{

    protected $table = 'sms';

    protected $fillable = [
        'message', 'date_envoie', 'date_programme', 'numeros', 'exercice_id'
    ];
    protected function casts(): array
    {
        return  [
            'date_envoie' => 'datetime', 'date_programme' => 'datetime', 'exercice_id' => 'integer'
        ];
    }
}
