<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SisContactTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sis_contacts')->insert([
            ['email' => 'commandant@sis.ch', 'liste' => 'facturation'],
            ['email' => 'secretaire@sis.ch', 'liste' => 'facturation'],
            ['email' => 'exercices@sis.ch', 'liste' => 'news'],
        ]);
    }
}
