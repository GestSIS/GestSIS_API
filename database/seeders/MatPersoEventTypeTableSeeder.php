<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MatPersoEventTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('materiel_event_types')->insert([
            ['id' => 1, 'nom' => 'Lavage', 'description' => '', 'validable' => false],
            ['id' => 2, 'nom' => 'Réparation', 'description' => '', 'validable' => false],
            ['id' => 3, 'nom' => 'Contrôle clé/badge', 'description' => 'Pour valider qu\'elle/il n\'est pas perdu.', 'validable' => true],
        ]);

        DB::table('materiel_event_type_pour')->insert([
            ['id' => 1, 'materiel_event_type_id' => 1, 'materiel_type_id' => 2],
            ['id' => 2, 'materiel_event_type_id' => 1, 'materiel_type_id' => 4],
            ['id' => 3, 'materiel_event_type_id' => 2, 'materiel_type_id' => 2],
            ['id' => 4, 'materiel_event_type_id' => 2, 'materiel_type_id' => 4],
            ['id' => 5, 'materiel_event_type_id' => 3, 'materiel_type_id' => 15],
        ]);
    }
}
