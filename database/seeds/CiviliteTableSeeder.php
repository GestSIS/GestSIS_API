<?php

use Illuminate\Database\Seeder;

class CiviliteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('civilites')->insert([
            'designation' => 'Homme',
        ]);
        DB::table('civilites')->insert([
            'designation' => 'Femme',
        ]);
    }
}
