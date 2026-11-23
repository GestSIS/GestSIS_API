<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Nœud du périmètre d'un inventaire, figé au démarrage (copie de l'arbre des emplacements).
 *
 * `emplacement_id` pointe vers l'emplacement d'origine, null s'il a été supprimé depuis.
 * Créé une seule fois au démarrage, jamais modifié : pas de timestamps.
 */
class InventaireEmplacement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'inventaire_id',
        'parent_id',
        'emplacement_id',
        'designation',
        'tri',
    ];

    protected function casts(): array
    {
        return [
            'inventaire_id' => 'integer',
            'parent_id' => 'integer',
            'emplacement_id' => 'integer',
            'tri' => 'integer',
        ];
    }

    public function inventaire(): BelongsTo
    {
        return $this->belongsTo(Inventaire::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('tri');
    }

    public function emplacement(): BelongsTo
    {
        return $this->belongsTo(Emplacement::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(InventaireLigne::class);
    }
}
