<?php

namespace Database\Factories;

use App\Models\Groupe;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupeFactory extends Factory
{
    protected $model = Groupe::class;

    public function definition(): array
    {
        // Format: soit un numéro (90-100), soit "90-100" avec suffixe optionnel (s/n)
        $baseNumber = $this->faker->numberBetween(90, 100);
        $hasRange = $this->faker->boolean(30); // 30% chance d'avoir un range

        if ($hasRange) {
            $endNumber = $baseNumber + $this->faker->numberBetween(1, 10);
            $suffix = $this->faker->randomElement(['', 's', 'n']);
            $no = $baseNumber . '-' . $endNumber . $suffix;
        } else {
            $no = (string) $baseNumber;
        }

        return [
            'designation' => $this->faker->words(3, true),
            'no' => $no,
            'tri' => $this->faker->numberBetween(1, 100),
            'type' => $this->faker->numberBetween(0, 2),
            'parent_id' => null,
        ];
    }
}
