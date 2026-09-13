<?php

namespace App\Models;

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
        'compartiment',
        'est_etiquete',
        'est_unique',
        'designation',
        'immatriculation',
        'chassis',
        'statut',
        'date_fabrication'
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
            'statut' => 'boolean',
            'date_fabrication' => 'date',
        ];
    }

    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }

    public function materielType()
    {
        return $this->belongsTo(MaterielType::class, 'materiel_type_id');
    }

    public function lavages()
    {
        return $this->hasMany(Lavage::class);
    }

    public function emplacementRepresentee()
    {
        return $this->hasOne(Emplacement::class, 'article_id');
    }
}
