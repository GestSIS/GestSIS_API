<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Decompte extends Model
{
    protected $casts = [
        'exercice-comptable_id' => 'integer', 'avs_total' => 'decimal:2', 'ac_total' => 'decimal:2', 'total' => 'decimal:2'
    ];

    public function exerciceComptable()
    {
        return $this->belongsTo(ExerciceComptable::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
}
