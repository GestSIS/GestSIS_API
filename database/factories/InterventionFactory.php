<?php

namespace Database\Factories;

use App\Models\ExerciceComptable;
use App\Models\Intervention;
use App\Models\InterventionTraitement;
use App\Models\Localite;
use App\Models\Phase;
use App\Models\Sapeur;
use App\Models\StatFederal;
use App\Models\TypeIntervention;
use Illuminate\Database\Eloquent\Factories\Factory;

class InterventionFactory extends Factory
{
    protected $model = Intervention::class;

    public function definition(): array
    {
        $exerciceComptable = ExerciceComptable::inRandomOrder()->first();
        $year = $exerciceComptable?->annee ?? now()->year;

        $date = $this->faker->dateTimeBetween("$year-01-01", "$year-12-31");
        $dateFin = clone $date;
        $dateFin->modify('+' . $this->faker->numberBetween(1, 6) . ' hours');

        return [
            'date_debut' => $date->format('Y-m-d'),
            'heure_debut' => $date->format('H:i'),
            'lieu' => $this->faker->streetAddress,
            'objet' => $this->faker->randomElement(['Inondation', 'Incendie', 'Accident de la route', 'Fuite de gaz', 'Secours à personne']),
            'date_fin' => $dateFin->format('Y-m-d'),
            'heure_fin' => $dateFin->format('H:i'),
            'rapport_police' => 0,
            'degre' => $this->faker->numberBetween(1, 3),
            'sauve_personne' => $this->faker->numberBetween(0, 5),
            'sauve_animaux' => $this->faker->numberBetween(0, 2),
            'description' => $this->faker->text,
            'proprietaire' => $this->faker->name . "\n" . $this->faker->address,
            'responsable' => $this->faker->name . "\n" . $this->faker->address,
            'stat_nb' => 1,
            'statut' => 0,
            'exercice_comptable_id' => $exerciceComptable?->id ?? 1,
            'localite_id' => Localite::inRandomOrder()->first()?->id ?? 1,
            'type_intervention_id' => TypeIntervention::inRandomOrder()->first()?->id ?? 1,
            'sapeur_id' => Sapeur::inRandomOrder()->first()?->id ?? 1,
            'stat_federal_id' => StatFederal::inRandomOrder()->first()?->id ?? 1,
            'intervention_traitement_id' => InterventionTraitement::inRandomOrder()->first()?->id ?? 1,
            'date_imputation' => null,
        ];
    }

    public function configure(): static
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
