<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = ['debut', 'fin', 'titre', 'resume', 'sapeur_id', 'sapeur'];
    protected $casts = [
        'sapeur_id' => 'integer',
    ];

    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }

    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }
}
