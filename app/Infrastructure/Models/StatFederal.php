<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class StatFederal extends Model
{
    protected $fillable = ['designation', 'status', 'tri'];
}
