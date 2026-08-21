<?php

namespace App\Models;

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
        return [
            'sapeur_id' => 'integer',
            'sms_id' => 'integer'
        ];
    }

    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }
}
