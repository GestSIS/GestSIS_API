<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medecin;

class MedecinTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Medecin::factory()->count(10)->create();
    }
}
