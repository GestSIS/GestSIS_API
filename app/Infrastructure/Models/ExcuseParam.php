<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ExcuseParam extends Model
{
    protected $fillable = ['actif', 'delai_excuse', 'email_rappel', 'texte_email_rappel'];
    protected function casts(): array
    {
        return  ['actif' => 'boolean', 'delai_excuse' => 'integer', 'email_rappel' => 'boolean'];
    }
}
