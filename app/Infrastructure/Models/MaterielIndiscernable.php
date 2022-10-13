<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielIndiscernable extends Model
{
    use HasFactory;

    protected $fillable = ['remarque', 'quantite'];
    protected $casts = ['quantite' => 'integer'];

    public function materiel()
    {
        return $this->morphOne(MaterielPersonnel::class, 'materiel');
    }
}
