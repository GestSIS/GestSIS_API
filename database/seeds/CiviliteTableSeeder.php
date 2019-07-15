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
            'designation' => 'Homme', 'forme_politesse' => 'Monsieur'
        ]);
        DB::table('civilites')->insert([
            'designation' => 'Femme', 'forme_politesse' => 'Madame'
        ]);
    }
}
