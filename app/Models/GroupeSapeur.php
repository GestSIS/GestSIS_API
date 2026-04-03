<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupeSapeur extends Model
{
    use HasFactory;

    protected $table = 'groupe_sapeur';
    protected $fillable = ['sapeur_id', 'groupe_id'];

    protected function casts(): array
    {
        return [
            'groupe_id' => 'integer',
            'sapeur_id' => 'integer'
        ];
    }
}
