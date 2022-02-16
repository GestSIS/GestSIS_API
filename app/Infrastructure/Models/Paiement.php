<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = ['solde', 'indemnite', 'frais_forfaitaire', 'frais_effectif', 'amende', 'avs_ac', 'total', 'sapeur_id', 'decompte_id'];
    protected $casts = [
        'solde' => 'decimal:2', 'indemnite' => 'decimal:2', 'frais_forfaitire' => 'decimal:2', 'frais_effectif' => 'decimal:2', 'amende' => 'decimal:2',
        'avs_ac' => 'decimal:2', 'total' => 'decimal:2', 'sapeur_id' => 'integer', 'decompte_id' => 'integer',
    ];

    public function decompte()
    {
        return $this->belongsTo('App\Infrastructure\Models\Decompte');
    }

    public function sapeur()
    {
        return $this->belongsTo('App\Infrastructure\Models\Sapeur');
    }
}
