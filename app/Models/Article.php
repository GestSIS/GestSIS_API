<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'materiel_type_id',
        'numero',
        'uuid',
        'achat',
        'taille',
        'remarque',
        'sapeur_id',
        'emplacement_id',
        'compartiment',
        'est_etiquete',
        'est_unique',
        'designation',
        'immatriculation',
        'chassis',
        'date_peremption',
        'supprime_le',
        'supprime_par_sapeur_id',
        'remarque_suppression',
        'ajoute_par_sapeur_id',
        'disponible_intervention',
    ];

    protected function casts(): array
    {
        return [
            'sapeur_id' => 'integer',
            'emplacement_id' => 'integer',
            'materiel_type_id' => 'integer',
            'est_etiquete' => 'boolean',
            'est_unique' => 'boolean',
            'date_peremption' => 'date',
            'supprime_le' => 'date',
            'supprime_par_sapeur_id' => 'integer',
            'ajoute_par_sapeur_id' => 'integer',
            'disponible_intervention' => 'boolean',
        ];
    }

    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }

    public function emplacement()
    {
        return $this->belongsTo(Emplacement::class);
    }

    public function materielType()
    {
        return $this->belongsTo(MaterielType::class, 'materiel_type_id');
    }

    public function emplacementRepresentee()
    {
        return $this->hasOne(Emplacement::class, 'article_id');
    }

    /**
     * Articles non supprimés logiquement (rangés ou attribués).
     */
    public function scopeNonSupprimes(Builder $query): Builder
    {
        return $query->whereNull('supprime_le');
    }

    /**
     * Historique des attributions de l'article à des sapeurs.
     */
    public function attributions(): HasMany
    {
        return $this->hasMany(ArticleAttribution::class);
    }

    /**
     * Attribution en cours de l'article (au plus une, contrainte en base).
     */
    public function attributionOuverte(): HasOne
    {
        return $this->hasOne(ArticleAttribution::class)->whereNull('fin');
    }
}
