<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapeursTelephone extends Model
{
    protected $fillable = ['telephone_type_id', 'numero', 'rta', 'order'];

    public function telephoneType()
    {
        return $this->belongsTo('App\Models\TelephoneType');
    }
}
