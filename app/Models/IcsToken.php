<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IcsToken extends Model
{
    protected $fillable = ['sapeur_id', 'token'];

    protected function casts(): array
    {
        return [
            'sapeur_id' => 'integer',
        ];
    }

    use HasFactory;

    public function sapeur()
    {
        return $this->belongsTo(Sapeur::class);
    }
}
