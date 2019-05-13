<?php

use Illuminate\Database\Seeder;

class CommunesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('communes')->insert([
            'id' => 1,
            'designation' => 'Saulcy',
        ]);
        DB::table('communes')->insert([
            'id' => 2,
            'designation' => 'Haute-Sorne',
        ]);
        DB::table('communes')->insert([
            'id' => 3,
            'designation' => 'Boécourt',
        ]);
    }
}
