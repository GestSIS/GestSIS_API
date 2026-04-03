<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuyauDiametre extends Model
{
    protected $fillable = ['diametre'];
    protected function casts(): array
    {
        return [
            'diametre' => 'integer'
        ];
    }
}
