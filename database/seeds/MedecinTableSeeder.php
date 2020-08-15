<?php

use Illuminate\Database\Seeder;

class MedecinTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Infrastructure\Models\Medecin::class, 10)->create();
    }
}
