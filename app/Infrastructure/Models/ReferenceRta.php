<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceRta extends Model
{
    use HasFactory;

    protected $fillable = ['data', 'sapeur_id'];

    protected $primaryKey = 'sapeur_id';
}
