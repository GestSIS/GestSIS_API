<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielEventType extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'description', 'validable'];
    protected $casts = ['validable' => 'boolean'];

    function materielTypes()
    {
        return $this->belongsToMany(MaterielType::class, 'materiel_event_type_pour');
    }

    public function materielTypeIds()
    {
        return $this->materielTypes()->pluck('materiel_type_id');
    }
}
