<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une pièce d'un inventaire : attendue au démarrage ou trouvée en plus.
 *
 * - `origine` : ATTENDU | EN_PLUS ;
 * - `statut` : null (à vérifier) | PRESENT | ABSENT | DEFECTUEUX ;
 * - `decision` : null (à traiter, ou pas un écart) | SORTIR_DU_STOCK | AJOUTER_AU_STOCK | AUCUNE_ACTION.
 *
 * Un écart est une ligne absente, défectueuse ou en plus ; la décision du responsable est
 * portée par chaque ligne de l'écart traité. `numero`, `designation`, `compartiment`, `taille`
 * et `date_peremption` sont figés au démarrage ; `materiel_type_id` null = type à créer au
 * traitement.
 *
 * Les pièces non numérotées qui partagent les valeurs de `CHAMPS_LOT` forment un lot :
 * l'API le compte (scope `duLot`), l'écran le regroupe par l'attribut `lot`.
 */
class InventaireLigne extends Model
{
    use HasFactory;

    /** Ce qui distingue un lot d'un autre : nœud, type, désignation, compartiment et variante. */
    public const CHAMPS_LOT = ['inventaire_emplacement_id', 'materiel_type_id', 'designation', 'compartiment', 'taille', 'date_peremption'];

    protected $appends = ['lot'];

    protected $fillable = [
        'inventaire_emplacement_id',
        'article_id',
        'origine',
        'statut',
        'materiel_type_id',
        'numero',
        'designation',
        'compartiment',
        'taille',
        'date_peremption',
        'est_etiquete',
        'verifie_par_sapeur_id',
        'verifie_le',
        'remarque_sapeur',
        'traite_par_sapeur_id',
        'traite_le',
        'remarque_traitement',
        'decision',
    ];

    protected function casts(): array
    {
        return [
            'inventaire_emplacement_id' => 'integer',
            'article_id' => 'integer',
            'materiel_type_id' => 'integer',
            'verifie_par_sapeur_id' => 'integer',
            'verifie_le' => 'datetime',
            'date_peremption' => 'date:Y-m-d',
            'est_etiquete' => 'boolean',
            'traite_par_sapeur_id' => 'integer',
            'traite_le' => 'datetime',
        ];
    }

    /**
     * Valeurs de `CHAMPS_LOT` de la ligne (péremption au format Y-m-d).
     *
     * @return array<string, mixed>
     */
    public function valeursLot(): array
    {
        return [...$this->only(self::CHAMPS_LOT), 'date_peremption' => $this->date_peremption?->toDateString()];
    }

    /**
     * Clé du lot d'une pièce non numérotée (null pour une pièce numérotée) : deux lignes
     * de même clé appartiennent au même lot.
     */
    protected function lot(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->numero === null
            ? json_encode(array_values($this->valeursLot()), JSON_UNESCAPED_UNICODE)
            : null);
    }

    /**
     * Pièces non numérotées du lot décrit par `$valeurs` (valeurs de `CHAMPS_LOT`).
     *
     * @param array<string, mixed> $valeurs
     */
    public function scopeDuLot(Builder $query, array $valeurs): Builder
    {
        $query->whereNull($query->qualifyColumn('numero'));
        foreach (self::CHAMPS_LOT as $champ) {
            if ($valeurs[$champ] === null) {
                $query->whereNull($query->qualifyColumn($champ));
            } else {
                $query->where($query->qualifyColumn($champ), $valeurs[$champ]);
            }
        }

        return $query;
    }

    /**
     * Écarts : pièces absentes ou défectueuses, et matériel trouvé en plus.
     */
    public function scopeEcarts(Builder $query): Builder
    {
        return $query->where(function (Builder $ecart): void {
            $ecart->whereIn($ecart->qualifyColumn('statut'), ['ABSENT', 'DEFECTUEUX'])
                ->orWhere($ecart->qualifyColumn('origine'), 'EN_PLUS');
        });
    }

    public function inventaireEmplacement(): BelongsTo
    {
        return $this->belongsTo(InventaireEmplacement::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function materielType(): BelongsTo
    {
        return $this->belongsTo(MaterielType::class);
    }
}
