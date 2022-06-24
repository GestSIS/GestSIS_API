<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndemniteInterventionTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $indemnites = array(
            array('designation' => 'Intervention', 'type' => '2', 'ecriture_categorie_id' => 4, 'compte_id' => 4, 'tarif' => 30, 'type_unite_id' => 2, 'tarif_min' => null, 'tarif_min_pour' => null, 'tarif_min_pro_rata' => false, 'phase_id' => 1, 'par_fonction' => false, 'taux_weekend' => null, 'taux_nuit' => null, 'debut' => null, 'fin' => null),
            array('designation' => 'Intervention', 'type' => '2', 'ecriture_categorie_id' => 4, 'compte_id' => 4, 'tarif' => 30, 'type_unite_id' => 2, 'tarif_min' => null, 'tarif_min_pour' => null, 'tarif_min_pro_rata' => false, 'phase_id' => null, 'par_fonction' => false, 'taux_weekend' => 1.25, 'taux_nuit' => 1.25, 'debut' => '20:00', 'fin' => '08:00'),
        );

        foreach ($indemnites as $item) {
            DB::table('indemnite_intervention_types')->insert($item);
        }
    }
}
