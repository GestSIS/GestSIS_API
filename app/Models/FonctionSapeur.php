<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FonctionSapeur extends Model
{
    use HasFactory;

    protected $table = 'fonction_sapeur';
    protected $fillable = ['sapeur_id', 'fonction_id', 'debut', 'fin', 'remarque'];

    /**
     * Le sapeur
     */
    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }

    /**
     * La fonction
     */
    public function fonction()
    {
        return $this->belongsTo(Fonction::class);
    }
}
