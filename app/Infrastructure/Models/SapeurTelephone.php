<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SapeurTelephone extends Model
{
    use HasFactory;

    protected $fillable = ['sapeur_id', 'telephone_type_id', 'numero', 'rta', 'priorite'];

    protected function casts(): array
    {
        return ['rta' => 'boolean', 'priorite' => 'integer', 'telephone_type_id' => 'integer', 'sapeur_id' => 'integer'];
    }

    protected $table = 'sapeur_telephone';

    public function telephoneType()
    {
        return $this->belongsTo(TelephoneType::class);
    }
}
