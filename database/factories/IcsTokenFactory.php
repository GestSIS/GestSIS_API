<?php

namespace Database\Factories;

use App\Models\IcsToken;
use App\Models\Sapeur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class IcsTokenFactory extends Factory
{
    protected $model = IcsToken::class;

    public function definition(): array
    {
        return [
            'sapeur_id' => Sapeur::inRandomOrder()->first()?->id ?? Sapeur::factory(),
            'token' => Str::random(48),
        ];
    }
}
