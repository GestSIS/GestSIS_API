<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    protected $table = 'sms';

    protected $fillable = [
        'message',
        'date_envoie',
        'date_programme',
        'exercice_id'
    ];
    protected function casts(): array
    {
        return [
            'date_envoie' => 'datetime',
            'date_programme' => 'datetime',
            'exercice_id' => 'integer'
        ];
    }

    /**
     * Les numéros contacté
     */
    public function smsNumeros()
    {
        return $this->hasMany(SmsNumero::class);
    }

    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }
}
