<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterielInterventionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('intervention_materiel')->insert([
            ['id' => 1, 'intervention_id' => 393, 'materiel_id' => 7, 'quantite' => 1],
            ['id' => 2, 'intervention_id' => 395, 'materiel_id' => 7, 'quantite' => 1],
            ['id' => 3, 'intervention_id' => 393, 'materiel_id' => 8, 'quantite' => 1],
            ['id' => 4, 'intervention_id' => 393, 'materiel_id' => 1, 'quantite' => 1],
            ['id' => 5, 'intervention_id' => 393, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 6, 'intervention_id' => 393, 'materiel_id' => 9, 'quantite' => 1],
            ['id' => 7, 'intervention_id' => 7000022, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 8, 'intervention_id' => 7000022, 'materiel_id' => 9, 'quantite' => 1],
            ['id' => 9, 'intervention_id' => 7000023, 'materiel_id' => 9, 'quantite' => 1],
            ['id' => 10, 'intervention_id' => 7000026, 'materiel_id' => 6, 'quantite' => 1],
            ['id' => 11, 'intervention_id' => 7000027, 'materiel_id' => 7, 'quantite' => 1],
            ['id' => 12, 'intervention_id' => 7000027, 'materiel_id' => 8, 'quantite' => 1],
            ['id' => 13, 'intervention_id' => 7000028, 'materiel_id' => 7, 'quantite' => 1],
            ['id' => 14, 'intervention_id' => 7000030, 'materiel_id' => 6, 'quantite' => 1],
            ['id' => 15, 'intervention_id' => 7000036, 'materiel_id' => 1, 'quantite' => 1],
            ['id' => 16, 'intervention_id' => 7000036, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 17, 'intervention_id' => 7000036, 'materiel_id' => 9, 'quantite' => 1],
            ['id' => 18, 'intervention_id' => 7000042, 'materiel_id' => 1, 'quantite' => 1],
            ['id' => 19, 'intervention_id' => 7000042, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 20, 'intervention_id' => 7000043, 'materiel_id' => 1, 'quantite' => 1],
            ['id' => 21, 'intervention_id' => 7000045, 'materiel_id' => 1, 'quantite' => 2],
            ['id' => 22, 'intervention_id' => 7000045, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 23, 'intervention_id' => 7000045, 'materiel_id' => 9, 'quantite' => 1],
            ['id' => 24, 'intervention_id' => 7000046, 'materiel_id' => 2, 'quantite' => 1],
            ['id' => 25, 'intervention_id' => 7000047, 'materiel_id' => 1, 'quantite' => 1],
            ['id' => 26, 'intervention_id' => 7000048, 'materiel_id' => 1, 'quantite' => 2],
            ['id' => 27, 'intervention_id' => 7000048, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 28, 'intervention_id' => 7000049, 'materiel_id' => 1, 'quantite' => 1],
            ['id' => 29, 'intervention_id' => 7000049, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 30, 'intervention_id' => 7000049, 'materiel_id' => 9, 'quantite' => 1],
            ['id' => 31, 'intervention_id' => 7000050, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 32, 'intervention_id' => 7000051, 'materiel_id' => 3, 'quantite' => 3],
            ['id' => 33, 'intervention_id' => 7000052, 'materiel_id' => 3, 'quantite' => 3],
            ['id' => 34, 'intervention_id' => 7000068, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 35, 'intervention_id' => 7000068, 'materiel_id' => 9, 'quantite' => 1],
            ['id' => 36, 'intervention_id' => 7000069, 'materiel_id' => 7, 'quantite' => 1],
            ['id' => 37, 'intervention_id' => 7000070, 'materiel_id' => 6, 'quantite' => 1],
            ['id' => 38, 'intervention_id' => 7000074, 'materiel_id' => 3, 'quantite' => 2],
            ['id' => 39, 'intervention_id' => 7000074, 'materiel_id' => 9, 'quantite' => 1],
            ['id' => 40, 'intervention_id' => 7000075, 'materiel_id' => 7, 'quantite' => 1],
            ['id' => 41, 'intervention_id' => 7000079, 'materiel_id' => 3, 'quantite' => 2],
            ['id' => 42, 'intervention_id' => 7000079, 'materiel_id' => 9, 'quantite' => 2],
            ['id' => 43, 'intervention_id' => 7000081, 'materiel_id' => 7, 'quantite' => 1],
            ['id' => 44, 'intervention_id' => 7000082, 'materiel_id' => 7, 'quantite' => 1],
            ['id' => 45, 'intervention_id' => 7000083, 'materiel_id' => 6, 'quantite' => 1],
            ['id' => 46, 'intervention_id' => 7000085, 'materiel_id' => 6, 'quantite' => 1],
            ['id' => 47, 'intervention_id' => 7000085, 'materiel_id' => 7, 'quantite' => 1],
        ]);
    }
}
