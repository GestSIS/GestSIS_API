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
        //Données de bases
        $this->call(CiviliteTableSeeder::class);
        $this->call(TelephoneTypeSeeder::class);
        $this->call(PermisTypeSeeder::class);

        $this->call(CommunesTableSeeder::class);
        $this->call(LocalitesTableSeeder::class);

        $this->call(FonctionTableSeeder::class);
        $this->call(GradesTableSeeder::class);
        $this->call(CoursTableSeeder::class);
        $this->call(GroupesTableSeeder::class);

        $this->call(ExcuseTypeTableSeeder::class);
        $this->call(ExerciceCategorieTableSeeder::class);

        //Données propres aux SIS de test
        $this->call(ExerciceComptableTableSeeder::class);
        $this->call(SapeursTableSeeder::class);
        $this->call(ExerciceTableSeeder::class);
        $this->call(ExerciceSapeurTableSeeder::class);
    }
}
