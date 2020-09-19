<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appel extends Model
{
    protected $fillable = ['numero', 'date', 'nom', 'commentaire'];

    use HasFactory;
}
