<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = ['solde', 'indemnite', 'frais_forfaitaire', 'frais_effectif', 'autre', 'avs_ac', 'total', 'sapeur_id', 'decompte_id'];
    protected function casts(): array
    {
        return  [
            'solde' => 'decimal:2', 'indemnite' => 'decimal:2', 'frais_forfaitaire' => 'decimal:2', 'frais_effectif' => 'decimal:2', 'autre' => 'decimal:2',
            'avs_ac' => 'decimal:2', 'total' => 'decimal:2', 'sapeur_id' => 'integer', 'decompte_id' => 'integer',
        ];
    }

    public function decompte()
    {
        return $this->belongsTo(Decompte::class);
    }

    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }
}
