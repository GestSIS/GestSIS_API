<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FonctionSapeur extends Model
{
    protected $table = 'fonction_sapeur';
    protected $fillable = ['debut', 'fin', 'remarque'];


    /**
     * Le sapeur
     */
    public function sapeur()
    {
        return $this->belongsTo('App\Models\Sapeur');
    }

    /**
     * La fonction
     */
    public function fonction()
    {
        return $this->belongsTo('App\Models\Fonction');
    }
}
