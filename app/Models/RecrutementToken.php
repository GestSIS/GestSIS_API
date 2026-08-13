<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Token de recrutement : un seul actif à la fois par SIS (une base tenant par SIS).
 * Le jeton en clair n'est jamais persisté, seul son hash l'est.
 * Logique métier dans App\Domaine\Business\RecrutementTokenBusiness.
 */
class RecrutementToken extends Model
{
    /** @use HasFactory<\Database\Factories\RecrutementTokenFactory> */
    use HasFactory;

    protected $fillable = ['token', 'expire_at'];

    protected function casts(): array
    {
        return [
            'expire_at' => 'datetime',
        ];
    }
}
