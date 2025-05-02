<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'attribution',
        'retour',
        'sapeur_id',
        'emplacement_id',
        'remarque',
        'compartiment',
        'est_etiquete',
        'est_unique',
    ];

    protected function casts(): array
    {
        return [
            'sapeur_id' => 'integer',
            'emplacement_id' => 'integer',
            'materiel_type_id' => 'integer',
            'attribution' => 'date',
            'retour' => 'date',
            'est_etiquete' => 'boolean',
            'est_unique' => 'boolean',
        ];
    }

    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }
}
