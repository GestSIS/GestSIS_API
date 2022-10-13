<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class MaterielAlertTypePourEvent extends Model
{
    protected $casts = ['materiel_alert_type_id' => 'integer', 'materiel_event_type_id' => 'integer'];
}
