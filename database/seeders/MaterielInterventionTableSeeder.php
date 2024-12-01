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
            ['id' => 22, 'intervention_id' => 393, 'materiel_id' => 6000000, 'quantite' => 1],
            ['id' => 23, 'intervention_id' => 395, 'materiel_id' => 6000000, 'quantite' => 1],
            ['id' => 24, 'intervention_id' => 393, 'materiel_id' => 3000000, 'quantite' => 1],
            ['id' => 7000016, 'intervention_id' => 393, 'materiel_id' => 1, 'quantite' => 1],
            ['id' => 7000017, 'intervention_id' => 393, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 7000018, 'intervention_id' => 393, 'materiel_id' => 6000001, 'quantite' => 1],
            ['id' => 7000019, 'intervention_id' => 7000022, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 7000020, 'intervention_id' => 7000022, 'materiel_id' => 6000001, 'quantite' => 1],
            ['id' => 7000021, 'intervention_id' => 7000023, 'materiel_id' => 6000001, 'quantite' => 1],
            ['id' => 7000022, 'intervention_id' => 7000026, 'materiel_id' => 6, 'quantite' => 1],
            ['id' => 7000023, 'intervention_id' => 7000027, 'materiel_id' => 6000000, 'quantite' => 1],
            ['id' => 7000024, 'intervention_id' => 7000027, 'materiel_id' => 3000000, 'quantite' => 1],
            ['id' => 7000025, 'intervention_id' => 7000028, 'materiel_id' => 6000000, 'quantite' => 1],
            ['id' => 7000026, 'intervention_id' => 7000030, 'materiel_id' => 6, 'quantite' => 1],
            ['id' => 7000027, 'intervention_id' => 7000036, 'materiel_id' => 1, 'quantite' => 1],
            ['id' => 7000028, 'intervention_id' => 7000036, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 7000029, 'intervention_id' => 7000036, 'materiel_id' => 6000001, 'quantite' => 1],
            ['id' => 7000030, 'intervention_id' => 7000042, 'materiel_id' => 1, 'quantite' => 1],
            ['id' => 7000031, 'intervention_id' => 7000042, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 7000032, 'intervention_id' => 7000043, 'materiel_id' => 1, 'quantite' => 1],
            ['id' => 7000033, 'intervention_id' => 7000045, 'materiel_id' => 1, 'quantite' => 2],
            ['id' => 7000034, 'intervention_id' => 7000045, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 7000035, 'intervention_id' => 7000045, 'materiel_id' => 6000001, 'quantite' => 1],
            ['id' => 7000036, 'intervention_id' => 7000046, 'materiel_id' => 2, 'quantite' => 1],
            ['id' => 7000037, 'intervention_id' => 7000047, 'materiel_id' => 1, 'quantite' => 1],
            ['id' => 7000038, 'intervention_id' => 7000048, 'materiel_id' => 1, 'quantite' => 2],
            ['id' => 7000039, 'intervention_id' => 7000048, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 7000040, 'intervention_id' => 7000049, 'materiel_id' => 1, 'quantite' => 1],
            ['id' => 7000041, 'intervention_id' => 7000049, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 7000042, 'intervention_id' => 7000049, 'materiel_id' => 6000001, 'quantite' => 1],
            ['id' => 7000043, 'intervention_id' => 7000050, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 7000044, 'intervention_id' => 7000051, 'materiel_id' => 3, 'quantite' => 3],
            ['id' => 7000045, 'intervention_id' => 7000052, 'materiel_id' => 3, 'quantite' => 3],
            ['id' => 7000046, 'intervention_id' => 7000068, 'materiel_id' => 3, 'quantite' => 1],
            ['id' => 7000047, 'intervention_id' => 7000068, 'materiel_id' => 6000001, 'quantite' => 1],
            ['id' => 7000048, 'intervention_id' => 7000069, 'materiel_id' => 6000000, 'quantite' => 1],
            ['id' => 7000049, 'intervention_id' => 7000070, 'materiel_id' => 6, 'quantite' => 1],
            ['id' => 7000050, 'intervention_id' => 7000074, 'materiel_id' => 3, 'quantite' => 2],
            ['id' => 7000051, 'intervention_id' => 7000074, 'materiel_id' => 6000001, 'quantite' => 1],
            ['id' => 7000052, 'intervention_id' => 7000075, 'materiel_id' => 6000000, 'quantite' => 1],
            ['id' => 7000053, 'intervention_id' => 7000079, 'materiel_id' => 3, 'quantite' => 2],
            ['id' => 7000054, 'intervention_id' => 7000079, 'materiel_id' => 6000001, 'quantite' => 2],
            ['id' => 7000055, 'intervention_id' => 7000081, 'materiel_id' => 6000000, 'quantite' => 1],
            ['id' => 7000056, 'intervention_id' => 7000082, 'materiel_id' => 6000000, 'quantite' => 1],
            ['id' => 7000057, 'intervention_id' => 7000083, 'materiel_id' => 6, 'quantite' => 1],
            ['id' => 7000058, 'intervention_id' => 7000085, 'materiel_id' => 6, 'quantite' => 1],
            ['id' => 7000059, 'intervention_id' => 7000085, 'materiel_id' => 6000000, 'quantite' => 1],
        ]);
    }
}
