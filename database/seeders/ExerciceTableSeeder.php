<?php

namespace Database\Seeders;

use App\Domaine\Business\ExerciceBusiness;
use App\Models\Exercice;
use App\Models\ExerciceComptable;
use App\Models\ExerciceSapeur;
use App\Models\Sapeur;
use Illuminate\Database\Seeder;

class ExerciceTableSeeder extends Seeder
{
    public function run(): void
    {
        $sapeurs = Sapeur::pluck('id');
        $exerciceComptableEnCoursId = ExerciceComptable::where('annee', now()->year)->value('id');

        $exercices = Exercice::factory(20)->create(['exercice_comptable_id' => $exerciceComptableEnCoursId]);

        // Le sapeur 1 correspond à l'utilisateur de base : on garantit sa présence à quelques exercices
        $exercicesAvecSapeur1 = $exercices->shuffle()->take(5)->pluck('id')->all();

        $exercices->each(function (Exercice $exercice) use ($sapeurs, $exercicesAvecSapeur1) {
            if ($sapeurs->isEmpty()) {
                return;
            }

            $assignedSapeurs = $sapeurs->shuffle()->take(fake()->numberBetween(5, 15));
            if (in_array($exercice->id, $exercicesAvecSapeur1) && $sapeurs->contains(1) && !$assignedSapeurs->contains(1)) {
                $assignedSapeurs->push(1);
            }

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

            ExerciceBusiness::updateStatut($exercice->id);
        });
    }
}
