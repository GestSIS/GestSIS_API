<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ControleExec extends Model
{
    protected $fillable = [
        'controle_id',
        'article_id',
        'executed_at',
        'executed_by',
        'trigger_type',
        'remarque_globale',
    ];

    protected function casts(): array
    {
        return [
            'controle_id' => 'integer',
            'article_id'  => 'integer',
            'executed_by' => 'integer',
            'executed_at' => 'datetime',
        ];
    }

    public function controle(): BelongsTo
    {
        return $this->belongsTo(Controle::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function executeur(): BelongsTo
    {
        return $this->belongsTo(Sapeur::class, 'executed_by');
    }

    public function execTaches(): HasMany
    {
        return $this->hasMany(ControleExecTache::class)->orderBy('task_order_snapshot');
    }

    public function isConforme(): bool
    {
        return $this->execTaches->every(function (ControleExecTache $t): bool {
            if ($t->statut !== null) {
                return $t->statut !== 'NOK';
            }
            return $t->value_in_range === true;
        });
    }
}
