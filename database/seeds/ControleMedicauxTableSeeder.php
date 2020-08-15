<?php

use Illuminate\Database\Seeder;

class ControleMedicauxTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Infrastructure\Models\ControleMedical::class, 50)->create();
    }
}
