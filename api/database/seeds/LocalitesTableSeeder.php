<?php

use Illuminate\Database\Seeder;

class LocalitesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('localites')->insert([
            'npa' => '2873',
            'designation' => 'Saulcy',
            'commune_id' => 1
        ]);
        DB::table('localites')->insert([
            'npa' => '2855',
            'designation' => 'Glovelier',
            'commune_id' => 2
        ]);
        DB::table('localites')->insert([
            'npa' => '2856',
            'designation' => 'Boécourt',
            'commune_id' => 3
        ]);
    }
}
