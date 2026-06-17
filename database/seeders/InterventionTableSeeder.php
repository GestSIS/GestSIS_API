<?php

namespace Database\Seeders;

use App\Models\Intervention;
use App\Models\Sapeur;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterventionTableSeeder extends Seeder
{
    public function run(): void
    {
        $sapeurs = Sapeur::pluck('id');

        Intervention::factory(15)->create()->each(function (Intervention $intervention) use ($sapeurs) {
            if ($sapeurs->isEmpty()) {
                return;
            }

            $debut = Carbon::parse($intervention->date_debut . ' ' . $intervention->heure_debut);
            $fin = Carbon::parse($intervention->date_fin . ' ' . $intervention->heure_fin);

            // Garantit que la plage est valide même si début = fin
            if ($fin <= $debut) {
                $fin = $debut->copy()->addHours(2);
            }

            $assignedSapeurs = $sapeurs->shuffle()->take(fake()->numberBetween(3, 8));
            foreach ($assignedSapeurs as $sapeurId) {
                $presenceDebut = $debut->copy()->addMinutes(fake()->numberBetween(0, 30));
                $presenceFin = $fin->copy()->subMinutes(fake()->numberBetween(0, 30));

                if ($presenceFin <= $presenceDebut) {
                    $presenceFin = $presenceDebut->copy()->addHour();
                }

                DB::table('intervention_sapeur')->insert([
                    'sapeur_id' => $sapeurId,
                    'intervention_id' => $intervention->id,
                    'debut' => $presenceDebut->format('Y-m-d H:i:s'),
                    'fin' => $presenceFin->format('Y-m-d H:i:s'),
                    'piquet' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
