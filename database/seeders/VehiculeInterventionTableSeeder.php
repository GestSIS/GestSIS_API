<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculeInterventionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('intervention_vehicule')->insert([
            ['id' => 5000000, 'intervention_id' => 393, 'vehicule_id' => 9],
            ['id' => 5000001, 'intervention_id' => 393, 'vehicule_id' => 7],
            ['id' => 5000002, 'intervention_id' => 393, 'vehicule_id' => 5],
            ['id' => 5000006, 'intervention_id' => 393, 'vehicule_id' => 12],
            ['id' => 5000010, 'intervention_id' => 393, 'vehicule_id' => 11],
            ['id' => 5000003, 'intervention_id' => 5000001, 'vehicule_id' => 9],
            ['id' => 5000004, 'intervention_id' => 5000001, 'vehicule_id' => 7],
            ['id' => 5000005, 'intervention_id' => 5000002, 'vehicule_id' => 7],
            ['id' => 5000007, 'intervention_id' => 5000002, 'vehicule_id' => 9],
            ['id' => 5000008, 'intervention_id' => 5000003, 'vehicule_id' => 5],
            ['id' => 5000009, 'intervention_id' => 5000003, 'vehicule_id' => 10],
            ['id' => 5000011, 'intervention_id' => 5000003, 'vehicule_id' => 7],
            ['id' => 5000012, 'intervention_id' => 5000003, 'vehicule_id' => 11],
            ['id' => 5000013, 'intervention_id' => 5000004, 'vehicule_id' => 9],
            ['id' => 5000014, 'intervention_id' => 5000004, 'vehicule_id' => 7],
            ['id' => 5000015, 'intervention_id' => 5000005, 'vehicule_id' => 5],
            ['id' => 5000016, 'intervention_id' => 5000005, 'vehicule_id' => 7],
            ['id' => 5000017, 'intervention_id' => 5000005, 'vehicule_id' => 12],
            ['id' => 5000018, 'intervention_id' => 5000006, 'vehicule_id' => 5],
            ['id' => 5000019, 'intervention_id' => 5000006, 'vehicule_id' => 7],
            ['id' => 5000020, 'intervention_id' => 5000007, 'vehicule_id' => 5],
            ['id' => 5000021, 'intervention_id' => 5000007, 'vehicule_id' => 7],
            ['id' => 5000022, 'intervention_id' => 5000007, 'vehicule_id' => 12],
            ['id' => 5000023, 'intervention_id' => 5000008, 'vehicule_id' => 5],
            ['id' => 5000024, 'intervention_id' => 5000008, 'vehicule_id' => 9],
            ['id' => 5000025, 'intervention_id' => 5000008, 'vehicule_id' => 10],
            ['id' => 5000026, 'intervention_id' => 5000008, 'vehicule_id' => 11],
            ['id' => 5000027, 'intervention_id' => 5000008, 'vehicule_id' => 7],
            ['id' => 5000028, 'intervention_id' => 5000009, 'vehicule_id' => 5],
            ['id' => 5000029, 'intervention_id' => 5000009, 'vehicule_id' => 7],
            ['id' => 5000030, 'intervention_id' => 5000009, 'vehicule_id' => 12],
            ['id' => 5000031, 'intervention_id' => 5000011, 'vehicule_id' => 7],
            ['id' => 5000032, 'intervention_id' => 5000012, 'vehicule_id' => 5],
            ['id' => 5000033, 'intervention_id' => 5000012, 'vehicule_id' => 9],
            ['id' => 7000043, 'intervention_id' => 7000024, 'vehicule_id' => 5],
            ['id' => 7000044, 'intervention_id' => 7000025, 'vehicule_id' => 9],
            ['id' => 7000045, 'intervention_id' => 7000025, 'vehicule_id' => 10],
            ['id' => 7000046, 'intervention_id' => 7000021, 'vehicule_id' => 5],
            ['id' => 7000047, 'intervention_id' => 7000021, 'vehicule_id' => 10],
            ['id' => 7000048, 'intervention_id' => 7000021, 'vehicule_id' => 12],
            ['id' => 7000049, 'intervention_id' => 7000021, 'vehicule_id' => 11],
            ['id' => 7000050, 'intervention_id' => 7000021, 'vehicule_id' => 7],
            ['id' => 7000051, 'intervention_id' => 7000021, 'vehicule_id' => 9],
        ]);
    }
}
