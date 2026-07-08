<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Decompte extends Model
{
    protected $fillable = [
        'exercice_comptable_id',
        'designation',
        'deduction',
        'date',
        'avs_total',
        'ac_total',
        'total',
        'a_payer_total',
        'a_facturer_total',
    ];

    protected function casts(): array
    {
        return [
            'exercice_comptable_id' => 'integer',
            'avs_total' => 'decimal:2',
            'ac_total' => 'decimal:2',
            'total' => 'decimal:2',
            'a_payer_total' => 'decimal:2',
            'a_facturer_total' => 'decimal:2'
        ];
    }

    public function exerciceComptable()
    {
        return $this->belongsTo(ExerciceComptable::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
}
