<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class FraisAnnuel extends Model
{
    protected $fillable = ['frais_annuel_type_id', 'type_unite_id',  'montant', 'quantite', 'fonction_id'];
}
