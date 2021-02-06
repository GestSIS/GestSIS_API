<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class SisParam extends Model
{
    protected $fillable = ['nom', 'rue','numero', 'district', 'no_arrondissement', 'telephone', 'email', 'localite_id', 'sapeur_id', 'bic', 'iban'];
}
