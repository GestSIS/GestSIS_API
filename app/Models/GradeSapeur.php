<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeSapeur extends Model
{
    use HasFactory;

    protected $table = 'grade_sapeur';
    protected $fillable = ['sapeur_id', 'grade_id', 'date', 'remarque'];

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
