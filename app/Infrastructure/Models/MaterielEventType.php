<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielEventType extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'description', 'validable'];
    protected function casts(): array
    {
        return  ['validable' => 'boolean'];
    }

    function materielTypes()
    {
        return $this->belongsToMany(MaterielType::class, 'materiel_event_type_pour');
    }

    public function materielTypeIds()
    {
        return $this->materielTypes()->pluck('materiel_type_id');
    }

    public function alerteTypes()
    {
        return $this->belongsToMany(MaterielAlerteType::class, 'materiel_alerte_type_pour', 'materiel_event_type_id', 'materiel_alerte_type_id');
    }
}
