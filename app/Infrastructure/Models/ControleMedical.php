<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControleMedical extends Model
{
    protected $table = 'controles_medicaux';

    protected $fillable = ['designation', 'consultation', 'validite', 'accepter', 'en_cours', 'medecin_id', 'controle_medical_type_id'];

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
        return $this->belongsTo('App\Infrastructure\Models\Sapeur');
    }

    /**
     * La médecin
     */
    public function medecin()
    {
        return $this->belongsTo('App\Infrastructure\Models\Medecin');
    }

    /**
     * Le type
     */
    public function type()
    {
        return $this->belongsTo('App\Infrastructure\Models\ControleMedicalType');
    }
}
