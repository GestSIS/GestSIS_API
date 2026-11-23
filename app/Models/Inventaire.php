<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Inventaire d'une branche d'emplacements, du démarrage à la validation par le sapeur.
 *
 * `statut` : EN_COURS | VALIDE.
 * Le périmètre figé est dans `inventaire_emplacements` ; sa racine est le nœud sans parent.
 */
class Inventaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'designation',
        'date',
        'statut',
        'remarque',
        'demarre_par_sapeur_id',
        'valide_par_sapeur_id',
        'valide_le',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'demarre_par_sapeur_id' => 'integer',
            'valide_par_sapeur_id' => 'integer',
            'valide_le' => 'datetime',
        ];
    }

    public function emplacements(): HasMany
    {
        return $this->hasMany(InventaireEmplacement::class)->orderBy('tri');
    }

    /**
     * Nœud racine du périmètre (celui sans parent).
     */
    public function racine(): HasOne
    {
        return $this->hasOne(InventaireEmplacement::class)->whereNull('parent_id');
    }

    /**
     * Lignes de l'inventaire, retrouvées par les nœuds du périmètre.
     */
    public function lignes(): HasManyThrough
    {
        return $this->hasManyThrough(InventaireLigne::class, InventaireEmplacement::class);
    }
}
