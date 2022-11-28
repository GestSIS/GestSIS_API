<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteCoursType extends Model
{
    protected $fillable = [
        'designation',
        'ecriture_categorie_id'
    ];
    protected $casts = [
        'ecriture_categorie_id' => 'integer'
    ];

    public function fonctions()
    {
        return $this->hasMany(IndemniteCoursFonction::class, 'indemnite_cours_id');
    }
}
