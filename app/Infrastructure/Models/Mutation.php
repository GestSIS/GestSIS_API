<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    protected $fillable = ['incorporation', 'sortie', 'motif', 'localite_id'];
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
