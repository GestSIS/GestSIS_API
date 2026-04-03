<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursSapeur extends Model
{
    use HasFactory;

    protected $fillable = ['cours_id', 'sapeur_id', 'date', 'localite_id', 'duree'];
    protected $table = 'cours_sapeur';
    protected function casts(): array
    {
        return [
            'localite_id' => 'integer',
            'duree' => 'decimal:2',
            'sapeur_id' => 'integer',
            'cours_id' => 'integer'
        ];
    }

    public function localite()
    {
        return $this->belongsTo(Localite::class);
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function ecritures()
    {
        return $this->hasMany(Ecriture::class);
    }
}
