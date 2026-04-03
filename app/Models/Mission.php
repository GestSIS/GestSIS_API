<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = ['debut', 'fin', 'titre', 'resume', 'sapeur_id', 'sapeur'];
    protected function casts(): array
    {
        return ['sapeur_id' => 'integer'];
    }

    public function sapeurObject()
    {
        return $this->belongsTo(Sapeur::class, 'sapeur_id');
    }

    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }
}
