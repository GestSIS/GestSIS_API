<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControleMaterielType extends Model
{
    protected $table = 'controle_materiel_types';

    protected $fillable = [
        'controle_id',
        'materiel_type_id',
    ];

    protected function casts(): array
    {
        return [
            'controle_id'      => 'integer',
            'materiel_type_id' => 'integer',
        ];
    }

    public function controle(): BelongsTo
    {
        return $this->belongsTo(Controle::class);
    }

    public function materielType(): BelongsTo
    {
        return $this->belongsTo(MaterielType::class);
    }
}
