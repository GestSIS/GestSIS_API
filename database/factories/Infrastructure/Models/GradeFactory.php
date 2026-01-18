<?php

namespace Database\Factories\Infrastructure\Models;

use App\Infrastructure\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        return [
            'designation' => $this->faker->words(2, true),
            'abreviation' => strtoupper($this->faker->lexify('??')),
            'groupe' => $this->faker->numberBetween(1, 5),
            'tri' => $this->faker->numberBetween(1, 100),
        ];
    }
}
