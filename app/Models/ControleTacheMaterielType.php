<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControleTacheMaterielType extends Model
{
    protected $table = 'controle_tache_materiel_types';

    protected $fillable = [
        'controle_tache_id',
        'materiel_type_id',
    ];

    protected function casts(): array
    {
        return [
            'controle_tache_id' => 'integer',
            'materiel_type_id'  => 'integer',
        ];
    }

    public function tache(): BelongsTo
    {
        return $this->belongsTo(ControleTache::class, 'controle_tache_id');
    }

    public function materielType(): BelongsTo
    {
        return $this->belongsTo(MaterielType::class);
    }
}
