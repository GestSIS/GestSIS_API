<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jalon extends Model
{
    /** @use HasFactory<\Database\Factories\JalonFactory> */
    use HasFactory;

    protected $fillable = ['titre', 'description', 'date_time', 'sapeur_id', 'sapeur'];

    protected function casts(): array
    {
        return ['sapeur_id' => 'integer'];
    }

    public function sapeurObject()
    {
        return $this->belongsTo(Sapeur::class, 'sapeur_id');
    }
}
