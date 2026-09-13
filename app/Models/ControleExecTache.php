<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControleExecTache extends Model
{
    protected $fillable = [
        'controle_exec_id',
        'tache_id',
        'task_order_snapshot',
        'statut',
        'value_measured',
        'value_min_snapshot',
        'value_max_snapshot',
        'value_in_range',
        'remarque',
    ];

    protected function casts(): array
    {
        return [
            'controle_exec_id'   => 'integer',
            'tache_id'           => 'integer',
            'task_order_snapshot'=> 'integer',
            'value_measured'     => 'decimal:4',
            'value_min_snapshot' => 'decimal:4',
            'value_max_snapshot' => 'decimal:4',
            'value_in_range'     => 'boolean',
        ];
    }

    public function exec(): BelongsTo
    {
        return $this->belongsTo(ControleExec::class, 'controle_exec_id');
    }

    public function tache(): BelongsTo
    {
        return $this->belongsTo(ControleTache::class, 'tache_id');
    }
}
