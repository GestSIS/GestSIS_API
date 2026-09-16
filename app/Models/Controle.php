<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Controle extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'recurrence_type',
        'recurrence_value',
        'duree_preavis',
        'externe',
        'reparateur',
        'nb_execution_max',
        'nb_execution_preavis',
    ];

    protected function casts(): array
    {
        return [
            'recurrence_type'      => 'string',
            'recurrence_value'     => 'integer',
            'duree_preavis'        => 'integer',
            'externe'              => 'boolean',
            'nb_execution_max'     => 'integer',
            'nb_execution_preavis' => 'integer',
        ];
    }

    public function taches(): HasMany
    {
        return $this->hasMany(ControleTache::class)->orderBy('order');
    }

    public function materielTypes(): HasMany
    {
        return $this->hasMany(ControleMaterielType::class);
    }

    public function execs(): HasMany
    {
        return $this->hasMany(ControleExec::class);
    }
}
