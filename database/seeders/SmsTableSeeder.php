<?php

namespace Database\Seeders;

use App\Models\Exercice;
use App\Models\ExerciceComptable;
use App\Models\Sapeur;
use App\Models\Sms;
use App\Models\SmsNumero;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SmsTableSeeder extends Seeder
{
    public function run(): void
    {
        $exerciceComptable = ExerciceComptable::where('annee', now()->year)->first();
        if ($exerciceComptable === null) {
            return;
        }

        $sapeurs = Sapeur::pluck('id');
        $exerciceIds = Exercice::where('exercice_comptable_id', $exerciceComptable->id)->pluck('id');

        for ($i = 0; $i < 10; $i++) {
            $differe = fake()->boolean(50);
            $dateProgramme = Carbon::parse(
                fake()->dateTimeBetween($exerciceComptable->debut, $exerciceComptable->fin),
            );

            $sms = Sms::create([
                'message' => fake()->sentence(),
                'date_programme' => $dateProgramme,
                'date_envoie' => $differe ? $dateProgramme->clone()->addHours(fake()->numberBetween(1, 48)) : $dateProgramme,
                'exercice_id' => $exerciceIds->isNotEmpty() && fake()->boolean(40) ? $exerciceIds->random() : null,
            ]);

            $destinataires = fake()->numberBetween(2, 6);
            for ($j = 0; $j < $destinataires; $j++) {
                $sapeurId = $sapeurs->isNotEmpty() && fake()->boolean(70) ? $sapeurs->random() : null;

                SmsNumero::create([
                    'sms_id' => $sms->id,
                    'sapeur_id' => $sapeurId,
                    'numero' => fake()->numerify('+4179#######'),
                ]);
            }
        }
    }
}
