<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielAlerteType extends Model
{
    use HasFactory;

    protected $fillable = ['titre', 'description', 'seuil_min', 'dernier'];
    protected $casts = ['seuil_min' => 'integer', 'dernier' => 'boolean'];

    function eventTypes()
    {
        return $this->belongsToMany(MaterielEventType::class, 'materiel_alerte_type_pour');
    }

    public function eventTypeIds()
    {
        return $this->eventTypes()->pluck('materiel_event_type_id');
    }
}
