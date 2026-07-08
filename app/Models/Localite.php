<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Localite extends Model
{
    protected $fillable = [
        'commune_id',
        'npa',
        'designation',
    ];

    protected function casts(): array
    {
        return [
            'commune_id' => 'integer'
        ];
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
}
