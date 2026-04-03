<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'no', 'designation', 'tri', 'parent_id'];
    protected function casts(): array
    {
        return [
            'tri' => 'integer',
            'parent_id' => 'integer',
            'type' => 'integer'
        ];
    }

    /**
     * The sapeur that belong to the sapeur.
     */
    public function sapeurIds()
    {
        return $this->hasMany(GroupeSapeur::class);
    }

    /**
     * The sapeur that belong to the sapeur.
     */
    public function sapeurs()
    {
        return $this->belongsToMany(Sapeur::class);
    }
}
