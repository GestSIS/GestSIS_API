<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConvocationParamTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('convocation_params')->insert([
            'titre'                => 'Convocation',
            'texte_debut'         => "Vous êtes convoqué(e) à l'exercice suivant :",
            'texte_fin'           => "En cas d'absence, veuillez vous annoncer auprès de votre responsable de section.",
            'texte_pour_info'     => 'Pour information',
            'affichage_pour_info' => true,
            'affichage_duree'     => true,
        ]);
    }
}
