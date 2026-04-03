<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciceSapeur extends Model
{
    protected $table = 'exercice_sapeur';
    protected $fillable = [
        'sapeur_id',
        'exercice_id',
        'convoque',
        'present',
        'absent',
        'remplace',
        'excuse_type_id',
        'excuse_statut',
        'date_demande',
        'date_validation',
        'justificatif_path',
        'justificatif_filename',
        'remarque',
        'justification'
    ];
    protected function casts(): array
    {
        return [
            'sapeur_id' => 'integer',
            'exercice_id' => 'integer',
            'present' => 'integer',
            'absent' => 'integer',
            'convoque' => 'integer',
            'remplace' => 'integer',
            'excuse_type_id' => 'integer',
            'excuse_statut' => 'integer'
        ];
    }

    use HasFactory;

    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }
}
