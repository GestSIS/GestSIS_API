<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControleMedical extends Model
{
    protected $table = 'controles_medicaux';

    protected $fillable = ['designation', 'consultation', 'validite', 'accepter', 'en_cours', 'medecin_id', 'controle_medical_type_id'];
    protected function casts(): array
    {
        return [
            'accepter' => 'boolean',
            'en_cours' => 'boolean',
            'medecin_id' => 'integer',
            'controle_medical_type_id' => 'integer',
        ];
    }

    use HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'designation' => '',
    ];

    /**
     * Le sapeur
     */
    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }

    /**
     * La médecin
     */
    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }

    /**
     * Le type
     */
    public function type()
    {
        return $this->belongsTo(ControleMedicalType::class);
    }
}
