<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeUniteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('type_unites')->insert([
            ['id' => '1', 'comptable' => True, 'unite' => 'pièce', 'abreviation' => 'pce'],
            ['id' => '2', 'comptable' => True, 'unite' => 'heure', 'abreviation' => 'h'],
            ['id' => '3', 'comptable' => True, 'unite' => 'an', 'abreviation' => 'an'],
            ['id' => '4', 'comptable' => True, 'unite' => 'km', 'abreviation' => 'km'],
            ['id' => '5', 'comptable' => True, 'unite' => 'jour', 'abreviation' => 'j'],
            ['id' => '6', 'comptable' => False, 'unite' => 'forfait', 'abreviation' => ''],
            ['id' => '7', 'comptable' => True, 'unite' => 'mois', 'abreviation' => 'm'],
        ]);
    }
}
