<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Localite extends Model
{
    protected function casts(): array
    {
        return  [
            'commune_id' => 'integer'
        ];
    }

    public function commune()
    {
        return $this->hasOne(Commune::class);
    }
}
