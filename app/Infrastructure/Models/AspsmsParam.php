<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class AspsmsParam extends Model
{
    protected $fillable = ['username', 'password', 'origin'];
}
