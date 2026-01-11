<?php

namespace Database\Seeders;

use App\Domaine\Business\ImputationBusiness;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndemniteInterventionTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('indemnite_intervention_types')->insert([
            ['designation' => 'Intervention', 'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE, 'ecriture_categorie_id' => 4, 'compte_id' => 4, 'tarif' => 30.0, 'type_unite_id' => 2, 'tarif_min' => 40.0, 'tarif_min_pour' => 1, 'tarif_min_pro_rata' => false, 'tarif_pro_rata' => false, 'phase_id' => 1, 'par_fonction' => false, 'taux_weekend' => null, 'taux_nuit' => null, 'debut' => null, 'fin' => null],
            ['designation' => 'Intervention', 'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE, 'ecriture_categorie_id' => 4, 'compte_id' => 4, 'tarif' => 30.0, 'type_unite_id' => 2, 'tarif_min' => null, 'tarif_min_pour' => null, 'tarif_min_pro_rata' => false, 'tarif_pro_rata' => false, 'phase_id' => null, 'par_fonction' => false, 'taux_weekend' => 1.25, 'taux_nuit' => 1.25, 'debut' => '20:00', 'fin' => '08:00'],
            ['designation' => 'Intervention Pro-Rata', 'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE, 'ecriture_categorie_id' => 4, 'compte_id' => 4, 'tarif' => 30.0, 'type_unite_id' => 2, 'tarif_min' => 40.0, 'tarif_min_pour' => 1, 'tarif_min_pro_rata' => true, 'tarif_pro_rata' => true, 'phase_id' => 1, 'par_fonction' => false, 'taux_weekend' => null, 'taux_nuit' => null, 'debut' => null, 'fin' => null],
        ]);
    }
}
