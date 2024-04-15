<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class GroupeSapeur extends Model
{
    protected $table = 'groupe_sapeur';
    protected function casts(): array
    {
        return  [
            'groupe_id' => 'integer', 'sapeur_id' => 'integer'
        ];
    }
}
