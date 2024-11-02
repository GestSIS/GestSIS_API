<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaintenanceTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $events = [
            ['id' => 1, 'designation' => 'Lavage', 'periodicite' => 0, 'nb_max' => 30, 'externalise' => false],
        ];

        DB::table('maintenance_types')->insert($events);

        $pour = [
            ['id' => 1, 'maintenance_type_id' => 1, 'materiel_type_id' => 2],
            ['id' => 2, 'maintenance_type_id' => 1, 'materiel_type_id' => 4],
        ];

        DB::table('maintenance_type_pour')->insert($pour);
    }
}
