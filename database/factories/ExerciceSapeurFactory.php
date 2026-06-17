<?php

namespace Database\Factories;

use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use App\Models\Sapeur;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExerciceSapeurFactory extends Factory
{
    protected $model = ExerciceSapeur::class;

    public function definition(): array
    {
        $present = $this->faker->boolean(80);

        return [
            'exercice_id' => Exercice::inRandomOrder()->first()?->id ?? Exercice::factory(),
            'sapeur_id' => Sapeur::inRandomOrder()->first()?->id ?? Sapeur::factory(),
            'convoque' => true,
            'present' => $present,
            'remplace' => false,
            'absent' => !$present,
            'excuse_statut' => null,
            'date_demande' => null,
            'remarque' => null,
            'justificatif_path' => null,
            'justificatif_filename' => null,
            'date_validation' => null,
            'justification' => null,
        ];
    }

    public function present(): static
    {
        return $this->state(['present' => true, 'absent' => false]);
    }

    public function absent(): static
    {
        return $this->state(['present' => false, 'absent' => true]);
    }
}
