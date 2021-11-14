<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class IndemniteAnnuel extends Model
{
    protected $fillable = ['indemnite_annuel_type_id', 'type_unite_id', 'montant', 'quantite', 'fonction_id'];
}
