<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceRta extends Model
{
    use HasFactory;

    protected $primaryKey = 'sapeur_id';
    protected $fillable = ['data', 'sapeur_id'];
    protected function casts(): array
    {
        return  [
            'sapeur_id' => 'integer'
        ];
    }
}
