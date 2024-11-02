<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NewSisDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Données de bases
        $this->call(CiviliteTableSeeder::class);
        $this->call(TelephoneTypeTableSeeder::class);
        $this->call(PermisTypeTableSeeder::class);

        $this->call(CommunesTableSeeder::class);
        $this->call(LocalitesTableSeeder::class);

        $this->call(GradesTableSeeder::class);
        $this->call(StatFederalTableSeeder::class);
        $this->call(TypeUniteTableSeeder::class);
        $this->call(PhaseTypeTableSeeder::class);

        // Exercices types
        $this->call(ExcuseTypeTableSeeder::class);
        $this->call(ExerciceCategorieTableSeeder::class);
        $this->call(FonctionTableSeeder::class);
        $this->call(CoursTableSeeder::class);
        $this->call(GroupesTableSeeder::class);

        // Param interventions
        $this->call(StatInterventionTableSeeder::class);
        $this->call(TypeInterventionTableSeeder::class);
        $this->call(InterventionTraitementTableSeeder::class);

        $this->call(TelephoneTableSeeder::class);
        $this->call(MissionTypeTableSeeder::class);

        // Frais
        $this->call(EcritureCategorieTableSeeder::class);

        // Controles médicaux
        $this->call(ControleMedicauxTypeTableSeeder::class);

        // Matériel personnel
        $this->call(MatPersoCategorieTableSeeder::class);
        $this->call(MaintenanceTypeTableSeeder::class);
    }
}
