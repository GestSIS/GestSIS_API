<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocaliteSis extends Model
{
    protected $table = 'localite_sis';

    protected $fillable = ['localite_id'];

    protected function casts(): array
    {
        return [
            'localite_id' => 'integer'
        ];
    }

    public function localite()
    {
        return $this->belongsTo(Localite::class);
    }
}
