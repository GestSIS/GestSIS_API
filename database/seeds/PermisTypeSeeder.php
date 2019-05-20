<?php

use Illuminate\Database\Seeder;

class PermisTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permis_types')->insert([
            'type' => 'A',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'A1',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'B',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'BE',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'B1',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'C',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'CE',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'C1',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'C1E',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'D',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'DE',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'D1',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'D1E',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'M',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'F',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'G',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'TPP',
        ]);
        DB::table('permis_types')->insert([
            'type' => 'OACP'
        ]);
    }
}
