<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeSapeur extends Model
{
    protected $table = 'grade_sapeur';
    protected $fillable = ['date', 'remarque'];

    /**
     * Le grade
     */
    public function grade()
    {
        return $this->belongsTo('App\Models\Grade');
    }

    /**
     * Le grade
     */
    public function sapeur()
    {
        return $this->belongsTo('App\Models\Sapeur');
    }
}
