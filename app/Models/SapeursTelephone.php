<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapeursTelephone extends Model
{
    public function telephoneType()
    {
        return $this->belongsTo('App\Models\TelephoneType');
    }
}
