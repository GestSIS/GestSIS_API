<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permis extends Model
{
    protected $fillable = ['date'];

    /**
     * Get the post that owns the comment.
     */
    public function sapeur()
    {
        return $this->belongsTo('App\Models\Sapeur');
    }

    /**
     * Get the post that owns the comment.
     */
    public function permisType()
    {
        return $this->belongsTo('App\Models\PermisType');
    }
}
