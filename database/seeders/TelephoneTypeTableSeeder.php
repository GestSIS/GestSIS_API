<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TelephoneTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('telephone_types')->insert([
            'type' => 'Privé',
        ]);
        DB::table('telephone_types')->insert([
            'type' => 'Professionnel',
        ]);
        DB::table('telephone_types')->insert([
            'type' => 'Portable',
        ]);
    }
}
