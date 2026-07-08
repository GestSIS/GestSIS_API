<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medecin extends Model
{
    use HasFactory;

    protected $fillable = ['designation', 'adresse', 'actif', 'localite_id'];
    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'localite_id' => 'integer'
        ];
    }

    /**
     * Le sapeur
     */
    public function localite()
    {
        return $this->belongsTo(Localite::class);
    }
}
