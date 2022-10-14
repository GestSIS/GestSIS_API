<?php

namespace App\Infrastructure\Models;

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
        return $this->belongsTo(Grade::class);
    }

    /**
     * Le grade
     */
    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }
}
