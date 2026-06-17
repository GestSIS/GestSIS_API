<?php

namespace Database\Seeders;

use App\Models\Exercice;
use App\Models\ExerciceSapeur;
use App\Models\Sapeur;
use Illuminate\Database\Seeder;

class ExerciceTableSeeder extends Seeder
{
    public function run(): void
    {
        $sapeurs = Sapeur::pluck('id');

        Exercice::factory(20)->create(['exercice_comptable_id' => 1])->each(function (Exercice $exercice) use ($sapeurs) {
            if ($sapeurs->isEmpty()) {
                return;
            }

            $assignedSapeurs = $sapeurs->shuffle()->take(fake()->numberBetween(5, 15));
            foreach ($assignedSapeurs as $sapeurId) {
                $present = fake()->boolean(80);
                ExerciceSapeur::create([
                    'exercice_id' => $exercice->id,
                    'sapeur_id' => $sapeurId,
                    'convoque' => true,
                    'present' => $present,
                    'remplace' => false,
                    'absent' => !$present,
                ]);
            }
        });
    }
}
