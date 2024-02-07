<?php

namespace Tests\Unit;

use App\Domaine\Business\ImputationBusiness;
use Exception;
use Tests\TestCase;

class ExampleTest extends TestCase
{

    protected $business;
    protected function setUp(): void
    {
        parent::setUp();
        $this->business = $this->app->make(ImputationBusiness::class);
    }


    // /**
    //  * Test imputer intervention avec taux nuit/week-end
    //  *
    //  * @return void
    //  * @throws Exception
    //  */
    // public function testImputerInterventionTauxOk()
    // {
    //     // TODO: A implémenter
    //     $intervention = [
    //         'date_debut' => null,
    //         'heure_debut' => null,
    //         'date_fin' => null,
    //         'heure_fin' => null,
    //         'lieu' => null,
    //         'objet' => null,
    //         'degre' => null,
    //         'description' => null,
    //         'statut' => null,
    //     ];
    //     $indemniteType = [
    //         'designation' => 'indemnite_demo',
    //         'tarif' => 20.0,
    //         'tarif_pro_rata' => false,
    //         'tarif_min' => null,
    //         'tarif_min_pour' => null,
    //         'tarif_min_pro_rata' => false,
    //         'taux_weekend' => 2,
    //         'taux_nuit' => 5,
    //         'debut' => '18:00',
    //         'fin' => '05:00',
    //         'compte_id' => 1,
    //         'phase_id' => null,
    //         'type_unite_id' => ImputationBusiness::UNITE_HEURE,
    //         'ecriture_categorie_id' => 12,
    //         'par_fonction' => null,
    //         'type' => null, # TODO: pas utilisé pour le moment
    //     ];
    //     $ecritures = $this->business->imputerInterventionTaux($intervention, $indemniteType);
    // }

    // public function testImputerInterventionTarifMinOk()
    // {
    //     $response = $this->json('GET', "/api/v2/exercices/");

    //     $response
    //         ->assertStatus(200)
    //         ->assertJsonStructure([
    //             'data' => [
    //                 '*' => [
    //                     'designation', 'localite_id', 'date', 'lieu', 'heure', 'duree'
    //                 ]
    //             ]
    //         ]);
    // }
}
