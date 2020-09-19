<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        $this->call(TelephoneTypeTableSeeder::class);
        $this->call(PermisTypeTableSeeder::class);

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

        //Exercices
        $this->call(ExerciceTableSeeder::class);
        $this->call(ExerciceSapeurTableSeeder::class);

        $this->call(MaterielTableSeeder::class);
        $this->call(VehiculeTableSeeder::class);
        $this->call(PhaseTypeTableSeeder::class);
        $this->call(StatFederalTableSeeder::class);
        $this->call(StatInterventionTableSeeder::class);
        $this->call(TypeInterventionTableSeeder::class);
        $this->call(InterventionTraitementTableSeeder::class);

        //Interventions
        $this->call(TypeUniteTableSeeder::class);
        $this->call(InterventionTableSeeder::class);
        $this->call(GroupeInterventionTableSeeder::class);
        $this->call(MaterielInterventionTableSeeder::class);
        $this->call(VehiculeInterventionTableSeeder::class);
        $this->call(SapeurInterventionTableSeeder::class);
        $this->call(MissionTableSeeder::class);
        $this->call(AppelTableSeeder::class);
        $this->call(PhaseTableSeeder::class);
        $this->call(QuittanceTableSeeder::class);
        $this->call(TelephoneTableSeeder::class);
        $this->call(MissionTypeTableSeeder::class);

        //Frais
        $this->call(CompteTableSeeder::class);
        $this->call(EcritureCategorieTableSeeder::class);
        $this->call(FraisAnnuelTypeTableSeeder::class);
        $this->call(IndemniteAnnuelTypeTableSeeder::class);
        $this->call(IndemniteExerciceTypeTableSeeder::class);
        $this->call(IndemniteExerciceFonctionTableSeeder::class);
        $this->call(IndemniteInterventionTypeTableSeeder::class);

        //Controles médicaux
        $this->call(MedecinTableSeeder::class);
        $this->call(ControleMedicauxTypeTableSeeder::class);
        $this->call(ControleMedicauxTableSeeder::class);
    }
}
