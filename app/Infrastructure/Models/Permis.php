<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permis extends Model
{
    use HasFactory;

    protected $fillable = ['sapeur_id', 'permis_type_id', 'date'];

    protected function casts(): array
    {
        return [
            'sapeur_id' => 'integer',
            'permis_type_id' => 'integer'
        ];
    }

    /**
     * Get the post that owns the comment.
     */
    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }

    /**
     * Get the post that owns the comment.
     */
    public function permisType()
    {
        return $this->belongsTo(PermisType::class);
    }
}
