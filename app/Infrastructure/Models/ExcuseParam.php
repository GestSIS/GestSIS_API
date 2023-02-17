<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class ExcuseParam extends Model
{
    protected $fillable = ['delai_excuse', 'email_rappel', 'texte_email_rappel'];
    protected $casts = ['delai_excuse' => 'integer', 'email_rappel' => 'boolean'];
}
