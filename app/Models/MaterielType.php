<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielType extends Model
{
    use HasFactory;

    protected $fillable = [
        'designation',
        'materiel_categorie_id',
        'type',
        'est_emplacement',
        'est_numerote',
        'est_attribuable',
        'est_taillee',
        'est_lavable',
        'a_batterie',
        'prix',
        'fournisseur',
        'numero_fournisseur',
        'reparateur',
        'a_controller',
        'remarque',
        'tri',
        'prefix',
    ];
    protected function casts(): array
    {
        return [
            'materiel_categorie_id' => 'integer',
            'fonction_id' => 'integer',
            'type' => 'integer',
            'est_emplacement' => 'boolean',
            'est_numerote' => 'boolean',
            'est_attribuable' => 'boolean',
            'est_taillee' => 'boolean',
            'est_lavable' => 'boolean',
            'a_batterie' => 'boolean',
            'a_controller' => 'boolean',
        ];
    }

    public function batterie()
    {
        return $this->belongsTo(MaterielTypeBatterie::class, 'id');
    }

    public function tuyau()
    {
        return $this->belongsTo(MaterielTypeTuyau::class, 'id');
    }
}
