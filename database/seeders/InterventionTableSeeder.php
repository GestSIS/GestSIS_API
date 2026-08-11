<?php

namespace Database\Seeders;

use App\Models\Intervention;
use App\Models\Sapeur;
use App\Domaine\Business\InterventionBusiness;
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

            // Arrondit au quart d'heure suivant/précédent pour rester dans la plage de l'intervention
            $debutQuart = $debut->copy()->addMinutes((15 - $debut->minute % 15) % 15)->second(0);
            $finQuart = $fin->copy()->subMinutes($fin->minute % 15)->second(0);

            $assignedSapeurs = $sapeurs->shuffle()->take(fake()->numberBetween(3, 8));
            foreach ($assignedSapeurs as $sapeurId) {
                $presenceDebut = $debutQuart->copy()->addMinutes(15 * fake()->numberBetween(0, 2));
                $presenceFin = $finQuart->copy()->subMinutes(15 * fake()->numberBetween(0, 2));

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

            $intervention->update(['statut' => InterventionBusiness::INTERVENTION_STATUT_SAISI]);
        });
    }
}
