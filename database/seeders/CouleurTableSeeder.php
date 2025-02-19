<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouleurTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('couleurs')->insert([
            ['id' => 1, 'nom' => 'E: Boncourt','texte' => '000000FF','fond' => '#64B5F6FF'],
            ['id' => 2, 'nom' => 'E: Courtem./Courchav.','texte' => '000000FF','fond' => '#F44336FF'],
            ['id' => 3, 'nom' => 'E: Buix/Montignez','texte' => '000000FF','fond' => '#81C784FF'],
            ['id' => 4, 'nom' => 'V: principaux','texte' => '000000FF','fond' => '#FFEB3BFF'],
            ['id' => 5, 'nom' => 'V: secondaires','texte' => '000000FF','fond' => '#FFFFFFFF'],
            ['id' => 6, 'nom' => 'V: MPT2','texte' => 'FFFFFFFF','fond' => '#000000FF'],
            ['id' => 7, 'nom' => 'V: Hydro/Inond','texte' => '000000FF','fond' => '#FF9800FF'],
            ['id' => 8, 'nom' => 'E: JSP','texte' => '000000FF','fond' => '#FFFFFFFF'],
            ['id' => 9, 'nom' => 'E: Stock','texte' => '000000FF','fond' => '#FFFFFFFF'],
            ['id' => 10, 'nom' => 'C: Motopompes/Véhicules','texte' => '000000FF','fond' => '#81C784FF'],
            ['id' => 11, 'nom' => 'C: Extinction','texte' => '000000FF','fond' => '#FF5722FF'],
            ['id' => 12, 'nom' => 'C: Assistance Technique','texte' => '000000FF','fond' => '#FFEB3BFF'],
            ['id' => 13, 'nom' => 'C: Anti-chutes','texte' => '000000FF','fond' => '#BA68C8FF'],
            ['id' => 14, 'nom' => 'C: Protection Respiratoire','texte' => '000000FF','fond' => '#64B5F6FF'],
            ['id' => 15, 'nom' => 'C: Hydro/Inond','texte' => '000000FF','fond' => '#FF9800FF'],
        ]);
    }
}
