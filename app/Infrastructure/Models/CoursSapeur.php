<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class CoursSapeur extends Model
{
    protected $fillable = ['date', 'localite_id', 'duree'];
    protected $casts = [
        'localite_id' => 'integer', 'duree' => 'decimal:2', 'sapeur_id' => 'integer', 'cours_id' => 'integer'
    ];
    protected $table = 'cours_sapeur';

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function ecritures()
    {
        return $this->hasMany(Ecriture::class);
    }
}
