<?php

namespace Database\Seeders;

use App\Models\Cours;
use App\Models\Fonction;
use App\Models\Grade;
use App\Models\Groupe;
use App\Models\Localite;
use App\Models\PermisType;
use App\Models\Sapeur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SapeursTableSeeder extends Seeder
{
    public function run(): void
    {
        // La factory crée automatiquement la mutation d'incorporation via afterCreating
        $sapeurs = Sapeur::factory(30)->create();

        $grades = Grade::pluck('id');
        $fonctions = Fonction::pluck('id');
        $groupes = Groupe::pluck('id');
        $cours = Cours::pluck('id');
        $localites = Localite::pluck('id');
        $permisTypes = PermisType::pluck('id', 'type');

        // Probabilité d'obtention par type de permis : forte pour le B, plus faible pour les autres.
        $permisProbabilities = [
            'B' => 95,
            'BE' => 30,
            'C1' => 25,
            'C1 118' => 20,
            'C' => 15,
            'CE' => 10,
            'C1E' => 8,
            'D1' => 8,
            'D' => 5,
            'A' => 20,
            'A1' => 15,
            'F' => 10,
            'G' => 10,
            'M' => 10,
        ];

        foreach ($sapeurs as $sapeur) {
            // Téléphones (1 ou 2 par sapeur)
            DB::table('sapeur_telephone')->insert([
                ['sapeur_id' => $sapeur->id, 'telephone_type_id' => 1, 'numero' => fake()->numerify('0## ### ## ##'), 'priorite' => 1, 'rta' => false],
                ['sapeur_id' => $sapeur->id, 'telephone_type_id' => 2, 'numero' => fake()->numerify('07# ### ## ##'), 'priorite' => 2, 'rta' => fake()->boolean(60)],
            ]);

            // Grade actuel
            if ($grades->isNotEmpty()) {
                DB::table('grade_sapeur')->insert([
                    'sapeur_id' => $sapeur->id,
                    'grade_id' => $grades->random(),
                    'date' => fake()->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
                    'remarque' => '',
                ]);
            }

            // Fonction actuelle
            if ($fonctions->isNotEmpty()) {
                DB::table('fonction_sapeur')->insert([
                    'sapeur_id' => $sapeur->id,
                    'fonction_id' => $fonctions->random(),
                    'debut' => fake()->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
                    'fin' => null,
                    'remarque' => '',
                ]);
            }

            // 1 à 3 groupes par sapeur
            if ($groupes->isNotEmpty()) {
                $assignedGroupes = $groupes->shuffle()->take(fake()->numberBetween(1, 3));
                foreach ($assignedGroupes as $groupeId) {
                    DB::table('groupe_sapeur')->insert([
                        'sapeur_id' => $sapeur->id,
                        'groupe_id' => $groupeId,
                    ]);
                }
            }

            // 0 à 2 cours par sapeur
            if ($cours->isNotEmpty() && fake()->boolean(70)) {
                $assignedCours = $cours->shuffle()->take(fake()->numberBetween(1, 2));
                foreach ($assignedCours as $coursId) {
                    DB::table('cours_sapeur')->insert([
                        'sapeur_id' => $sapeur->id,
                        'cours_id' => $coursId,
                        'duree' => fake()->randomElement([1, 2, 3]),
                        'date' => fake()->dateTimeBetween('-8 years', '-6 months')->format('Y-m-d'),
                        'localite_id' => $localites->random(),
                    ]);
                }
            }

            // Permis : forte probabilité pour le B, plus faible pour les autres (C1/C1 118, etc.)
            foreach ($permisProbabilities as $type => $probability) {
                if (isset($permisTypes[$type]) && fake()->boolean($probability)) {
                    DB::table('permis')->insert([
                        'sapeur_id' => $sapeur->id,
                        'permis_type_id' => $permisTypes[$type],
                        'date' => fake()->dateTimeBetween('-15 years', '-1 year')->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
