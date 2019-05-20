<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(TelephoneTypeSeeder::class);
        $this->call(PermisTypeSeeder::class);
        $this->call(CommunesTableSeeder::class);
        $this->call(LocalitesTableSeeder::class);
        $this->call(GradesTableSeeder::class);
        $this->call(CoursTableSeeder::class);
        $this->call(SapeursTableSeeder::class);
    }
}
