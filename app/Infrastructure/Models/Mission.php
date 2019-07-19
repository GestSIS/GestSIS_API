<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    protected $fillable = ['debut', 'fin', 'titre', 'resume', 'sapeur_id'];
}
