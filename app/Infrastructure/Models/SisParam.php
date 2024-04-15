<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class SisParam extends Model
{
    protected $fillable = ['nom', 'rue', 'numero', 'district', 'no_arrondissement', 'telephone', 'email', 'localite_id', 'sapeur_id', 'bic', 'iban'];
    protected function casts(): array
    {
        return  ['localite_id' => 'integer', 'sapeur_id' => 'integer'];
    }

    public function localite()
    {
        return $this->belongsTo(Localite::class);
    }

    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }
}
