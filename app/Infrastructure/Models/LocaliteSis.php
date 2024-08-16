<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class LocaliteSis extends Model
{
    protected $table = 'localite_sis';

    protected function casts(): array
    {
        return  [
            'localite_id' => 'integer'
        ];
    }

    public function localite()
    {
        return $this->hasOne(Localite::class);
    }
}
