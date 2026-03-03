<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseTestSeeder extends Seeder
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

        // $this->call(GradesTableSeeder::class);
        // $this->call(StatFederalTableSeeder::class);
        // $this->call(TypeUniteTableSeeder::class);
        // $this->call(PhaseTypeTableSeeder::class);

        // /**/
        // // Exercices types
        // $this->call(ExcuseTypeTableSeeder::class);
        // $this->call(ExerciceCategorieTableSeeder::class);
        // $this->call(FonctionTableSeeder::class);
        // $this->call(CoursTableSeeder::class);
        // $this->call(GroupesTableSeeder::class);
        // /**/

        // // Données propres aux SIS de test
        $this->call(ExerciceComptableTableSeeder::class);

        // // Matériel
        // $this->call(CouleurTableSeeder::class);
        // $this->call(BatterieTypeTableSeeder::class);
        // $this->call(TuyauDiametreTableSeeder::class);
        // $this->call(MaterielCategorieTableSeeder::class);
        // $this->call(MaterielTypeTableSeeder::class);
        // $this->call(MaintenanceTypeTableSeeder::class);

        // // Materiel
        // $this->call(EmplacementTableSeeder::class);
        // $this->call(HangarTableSeeder::class);
        // $this->call(VehiculeTableSeeder::class);
        // $this->call(ArticleTableSeeder::class);

        // // Param interventions
        // $this->call(MaterielTableSeeder::class);
        // $this->call(StatInterventionTableSeeder::class);
        // $this->call(TypeInterventionTableSeeder::class);
        // $this->call(InterventionTraitementTableSeeder::class);

        // // Interventions
        // $this->call(InterventionTableSeeder::class);
        // $this->call(GroupeInterventionTableSeeder::class);
        // $this->call(MaterielInterventionTableSeeder::class);
        // $this->call(VehiculeInterventionTableSeeder::class);
        // $this->call(SapeurInterventionTableSeeder::class);
        // $this->call(MissionTableSeeder::class);
        // $this->call(AppelTableSeeder::class);
        // $this->call(PhaseTableSeeder::class);
        // $this->call(QuittanceTableSeeder::class);
        // $this->call(TelephoneTableSeeder::class);
        // $this->call(MissionTypeTableSeeder::class);

        // // Frais
        // $this->call(CompteTableSeeder::class);
        // $this->call(EcritureCategorieTableSeeder::class);
        // $this->call(FraisIndemniteAnnuelTypeTableSeeder::class);
        // $this->call(IndemniteExerciceTypeTableSeeder::class);
        // $this->call(IndemniteExerciceFonctionTableSeeder::class);
        // $this->call(IndemniteInterventionTypeTableSeeder::class);
        // $this->call(IndemniteCoursTypeTableSeeder::class);
        // $this->call(TravailTypeTableSeeder::class);
        // $this->call(AmendeTableSeeder::class);

        // // Params
        // $this->call(AvsParamTableSeeder::class);
        // $this->call(SisParamTableSeeder::class);

        // // Controles médicaux
        // $this->call(MedecinTableSeeder::class);
        // $this->call(ControleMedicauxTypeTableSeeder::class);
        // $this->call(ControleMedicauxTableSeeder::class);

        // $this->call(DecompteTableSeeder::class);
        // $this->call(PaiementTableSeeder::class);
    }
}
