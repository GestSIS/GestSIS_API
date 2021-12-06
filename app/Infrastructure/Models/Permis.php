<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Permis extends Model
{
    protected $fillable = ['date'];
    protected $casts = [
        'sapeur_id' => 'integer', 'permis_type_id' => 'integer'
    ];

    /**
     * Get the post that owns the comment.
     */
    public function sapeur()
    {
        return $this->belongsTo('App\Infrastructure\Models\Sapeur');
    }

    /**
     * Get the post that owns the comment.
     */
    public function permisType()
    {
        return $this->belongsTo('App\Infrastructure\Models\PermisType');
    }
}
