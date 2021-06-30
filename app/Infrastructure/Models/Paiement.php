<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    public function decompte()
    {
        return $this->belongsTo('App\Infrastructure\Models\Decompte');
    }

    public function sapeur()
    {
        return $this->belongsTo('App\Infrastructure\Models\Sapeur');
    }
}
