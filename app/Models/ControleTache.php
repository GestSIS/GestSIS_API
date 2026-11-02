<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ControleTache extends Model
{
    protected $fillable = [
        'controle_id',
        'order',
        'nom',
        'description',
        'type',
        'obligatoire',
        'unit',
        'value_min',
        'value_max',
    ];

    protected function casts(): array
    {
        return [
            'controle_id' => 'integer',
            'order'       => 'integer',
            'obligatoire' => 'boolean',
            'value_min'   => 'decimal:4',
            'value_max'   => 'decimal:4',
        ];
    }

    public function controle(): BelongsTo
    {
        return $this->belongsTo(Controle::class);
    }

    /**
     * Types d'articles acceptés par une tâche COMPOSANT.
     */
    public function materielTypes(): HasMany
    {
        return $this->hasMany(ControleTacheMaterielType::class);
    }
}
