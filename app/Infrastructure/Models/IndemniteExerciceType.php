<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteExerciceType extends Model
{
    protected $fillable = [
        'designation',
        'type_unite_id',
        'par_fonction',
        'ecriture_categorie_id'
    ];
    protected function casts(): array
    {
        return  [
            'type_unite_id' => 'integer', 'par_fonction' => 'boolean', 'ecriture_categorie_id' => 'integer'
        ];
    }

    public function fonctions()
    {
        return $this->hasMany(IndemniteExerciceFonction::class, 'indemnite_exe_id');
    }
}
