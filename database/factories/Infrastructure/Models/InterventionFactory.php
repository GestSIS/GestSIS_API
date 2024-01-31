<?php

namespace Database\Factories\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Infrastructure\Models\Intervention;
use App\Infrastructure\Models\Phase;

class InterventionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Intervention::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeThisYear();
        $dateTwo = clone $date;

        return [
            'date_debut' => $date->format('Y-m-d'),
            'heure_debut' => $date->format('H:m'),
            'lieu' => $this->faker->streetAddress,
            'objet' => $this->faker->randomElement(['Inondation', 'Incendie']),
            'date_fin' => $dateTwo->format('Y-m-d'),
            'heure_fin' => $dateTwo->format('H:m'),
            'rapport_police' => 0,
            'degre' => $this->faker->numberBetween(1, 3),
            'sauve_personne' => $this->faker->numberBetween(0, 5),
            'sauve_animaux' => $this->faker->numberBetween(0, 2),
            'description' => $this->faker->text,
            'proprietaire' => $this->faker->name . '\n' . $this->faker->address,
            'responsable' => $this->faker->name . '\n' . $this->faker->address,
            'stat_nb' => 1,
            'statut' => 0,
            'exercice_comptable_id' => 4,
            'localite_id' => $this->faker->randomElement($array = array(3, 5, 23, 44, 93)),
            'type_intervention_id' => $this->faker->numberBetween(1, 4),
            'sapeur_id' => $this->faker->numberBetween(1, 10),
            'stat_federal_id' => $this->faker->numberBetween(1, 5),
            'intervention_traitement_id' => 1,
            'date_imputation' => null
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Intervention $intervention) {
            $phase = new Phase();
            $phase->intervention_id = $intervention->id;
            $phase->phase_type_id = 1;
            $phase->debut = null;
            $phase->save();
        });
    }
}
