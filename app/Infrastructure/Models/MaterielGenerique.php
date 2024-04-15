<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielGenerique extends Model
{
    use HasFactory;

    protected $fillable = ['quantite'];
    protected function casts(): array
    {
        return  ['quantite' => 'integer'];
    }

    public function materiel()
    {
        return $this->morphOne(MaterielPersonnel::class, 'materiel');
    }
}
