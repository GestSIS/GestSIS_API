<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for local development.
     *
     * Calls NewSisDatabaseSeeder for all reference and proposed-default data,
     * then adds realistic demo data so a developer can immediately use the application.
     */
    public function run(): void
    {
        $this->call(NewSisDatabaseSeeder::class);

        // Données financières de référence (comptes, indemnités, frais)
        $this->call(CompteTableSeeder::class);
        $this->call(FraisIndemniteAnnuelTypeTableSeeder::class);
        $this->call(IndemniteExerciceTypeTableSeeder::class);
        $this->call(IndemniteExerciceFonctionTableSeeder::class);
        $this->call(IndemniteInterventionTypeTableSeeder::class);
        $this->call(IndemniteInterventionFonctionTableSeeder::class);
        $this->call(IndemniteCoursTypeTableSeeder::class);
        $this->call(IndemniteCoursFonctionTableSeeder::class);
        $this->call(TravailTypeTableSeeder::class);
        $this->call(TravailTypeFonctionTableSeeder::class);
        $this->call(AmendeTableSeeder::class);
        $this->call(AvsParamTableSeeder::class);

        // Paramètres de configuration
        $this->call(AbsenceParamTableSeeder::class);
        $this->call(ConvocationParamTableSeeder::class);
        $this->call(ExcuseParamTableSeeder::class);
        $this->call(SisContactTableSeeder::class);

        // Matériel de référence
        $this->call(EmplacementTableSeeder::class);
        $this->call(HangarTableSeeder::class);
        $this->call(VehiculeTableSeeder::class);
        $this->call(ArticleTableSeeder::class);
        $this->call(MaterielTableSeeder::class);

        // Médecins de référence
        $this->call(MedecinTableSeeder::class);

        // Exercices comptables et données de dev
        $this->call(ExerciceComptableTableSeeder::class);

        $this->call(SapeursTableSeeder::class);

        // SisParam référence sapeur_id=1, doit être après SapeursTableSeeder
        $this->call(SisParamTableSeeder::class);

        $this->call(ExerciceTableSeeder::class);
        $this->call(InterventionTableSeeder::class);
    }
}
