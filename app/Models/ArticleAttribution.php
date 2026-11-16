<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Période d'attribution d'un article à un sapeur (`fin` à null = en cours).
 */
class ArticleAttribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'sapeur_id',
        'debut',
        'fin',
        'emplacement_retour_id',
        'attribue_par_sapeur_id',
        'cloture_par_sapeur_id',
    ];

    protected function casts(): array
    {
        return [
            'article_id' => 'integer',
            'sapeur_id' => 'integer',
            'debut' => 'date',
            'fin' => 'date',
            'emplacement_retour_id' => 'integer',
            'attribue_par_sapeur_id' => 'integer',
            'cloture_par_sapeur_id' => 'integer',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function sapeur(): BelongsTo
    {
        return $this->belongsTo(Sapeur::class);
    }
}
