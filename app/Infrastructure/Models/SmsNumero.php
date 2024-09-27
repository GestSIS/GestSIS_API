<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class SmsNumero extends Model
{

    protected $fillable = [
        'numero',
        'sapeur_id',
        'sms_id'
    ];
    protected function casts(): array
    {
        return  [
            'sapeur_id' => 'integer',
            'sms_id' => 'integer'
        ];
    }
}
