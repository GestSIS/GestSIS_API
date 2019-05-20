<?php

use Illuminate\Database\Seeder;

class TelephoneTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
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
