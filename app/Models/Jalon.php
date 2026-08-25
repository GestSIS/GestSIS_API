<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jalon extends Model
{
    /** @use HasFactory<\Database\Factories\JalonFactory> */
    use HasFactory;

    protected $fillable = ['titre', 'description', 'date_time'];
}
