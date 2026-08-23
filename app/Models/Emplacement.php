<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'designation',
        'remarque',
        'tri',
        'est_etiquete',
        'est_compartimentable',
        'impression_inventaire',
        'couleur_id',
        'parent_id',
        'article_id',
        'statut'
    ];
    protected function casts(): array
    {
        return [
            'statut' => 'boolean',
            'tri' => 'integer',
            'impression_inventaire' => 'date',
            'est_etiquete' => 'boolean',
            'est_compartimentable' => 'boolean',
            'couleur_id' => 'integer',
            'parent_id' => 'integer',
            'article_id' => 'integer'
        ];
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function hangar()
    {
        return $this->belongsTo(Hangar::class, 'id');
    }
}
