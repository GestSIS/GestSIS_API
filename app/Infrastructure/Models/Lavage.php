<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lavage extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'date'
    ];

    protected function casts(): array
    {
        return [
            'article_id' => 'integer',
            'date' => 'date',
        ];
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
