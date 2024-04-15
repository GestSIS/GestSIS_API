<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciceSapeur extends Model
{
    protected $table = 'exercice_sapeur';
    protected $fillable = ['convoque', 'present', 'absent', 'remplace', 'excuse_type_id', 'amende'];
    protected function casts(): array
    {
        return  [
            'sapeur_id' => 'integer', 'exercice_id' => 'integer', 'present' => 'integer', 'absent' => 'integer', 'convoque' => 'integer',
            'amende' => 'boolean', 'remplace' => 'integer', 'excuse_type_id' => 'integer', 'excuse_statut' => 'integer'
        ];
    }

    use HasFactory;

    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }
}
