<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapeurTelephone extends Model
{
    protected $fillable = ['telephone_type_id', 'numero', 'rta', 'priorite'];

    protected $table = 'sapeur_telephone';

    public function telephoneType()
    {
        return $this->belongsTo('App\Models\TelephoneType');
    }
}
