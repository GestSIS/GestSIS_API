<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielNominal extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'numero', 'achat'];
    protected $casts = ['achat' => 'datetime'];

    public function materiel()
    {
        return $this->morphOne(MaterielPersonnel::class, 'materiel');
    }
}
