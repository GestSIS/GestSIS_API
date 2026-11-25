<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    protected $fillable = ['incorporation', 'sortie', 'motif', 'localite_id', 'sapeur_id', 'nature'];
    protected function casts(): array
    {
        return ['localite_id' => 'integer', 'sapeur_id' => 'integer', 'nature' => 'integer'];
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
