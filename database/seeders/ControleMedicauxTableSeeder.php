<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ControleMedical;

class ControleMedicauxTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        ControleMedical::factory()->count(50)->create();
    }
}
