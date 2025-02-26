<?php

namespace Tests\Feature;

use App\Infrastructure\Models\IndemniteInterventionType;
use App\Infrastructure\Models\Intervention;
use App\Infrastructure\Models\Sapeur;
use Exception;
use Tests\TestCase;
use App\Domaine\API\InterventionService;
use App\Domaine\API\SapeurService;
use App\Domaine\API\ImputationService;
use App\Domaine\Business\ImputationBusiness;
use App\Domaine\Business\InterventionBusiness;
use Carbon\Carbon;

class ImputationInterventionTest extends TestCase
{

    protected $comptabiliteService;
    protected $sapeurOneId;
    protected $sapeurTwoId;
    protected $sapeurThreeId;
    protected $interventionId;

    protected $interventionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->interventionService = $this->app->make(InterventionService::class);
        $sapeurService = $this->app->make(SapeurService::class);
        $this->comptabiliteService = $this->app->make(ImputationService::class);

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurOneId = $sapeurService->createSapeur($data)->id;

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurTwoId = $sapeurService->createSapeur($data)->id;

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurThreeId = $sapeurService->createSapeur($data)->id;

        $intervention = Intervention::factory()->make();
        $intervention->date_debut = Carbon::createMidnightDate(2019, 1, 1);
        $intervention->heure_debut = "00:00";
        $intervention->date_fin = Carbon::createMidnightDate(2019, 1, 3);
        $intervention->heure_fin = "00:00";

        $this->interventionId = $this->interventionService->createIntervention($intervention->toArray())->id;

        $sapeurs = array(
            array(
                'sapeur_id' => 1,
                'debut' => '2019-01-01 20:00',
                'fin' => '2019-01-02 12:15',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => 2,
                'debut' => '2019-01-01 12:00',
                'fin' => '2019-01-01 12:30',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => 3,
                'debut' => '2019-01-01 12:15',
                'fin' => '2019-01-01 15:30',
                'piquet' => 0
            ),
        );

        $this->interventionService->addPresences($this->interventionId, $sapeurs);
        $this->interventionService->validerInterventionById($this->interventionId);
    }

    // /**
    //  * Test add exercice
    //  *
    //  * @return void
    //  * @throws Exception
    //  */
    // public function testImputationImputationTarifSimpleSansProRata()
    // {
    //     $param = array(
    //         "indemnite_intervention_type_id" => 1
    //     );
    //     $response = $this->json('POST', "/api/v2/imputation/intervention/$this->interventionId", $param);

    //     $response
    //         ->assertStatus(200)
    //         ->assertJson(
    //             [
    //                 "data" => [
    //                     'statut' => InterventionBusiness::INTERVENTION_STATUT_IMPUTE,
    //                     'ecritures' => [
    //                         [
    //                             'complement' => Null,
    //                             'total' => '490.00',
    //                             'tarif' => '30.00',
    //                             'type_unite_id' => 2,
    //                             'quantite' => '16.25',
    //                             'tarif_min' => '40.00',
    //                             'tarif_pro_rata' => false,
    //                             'tarif_min_pour' => '1.00',
    //                             'tarif_min_pro_rata' => false,
    //                             'date' => '2019-01-01',
    //                             'heure' => '00:00:00',
    //                             'sapeur_id' => 1,
    //                             'compte_id' => 4,
    //                             'intervention_id' => $this->interventionId,
    //                             'exercice_id' => null,
    //                             'ecriture_categorie_id' => 4,
    //                             'cours_sapeur_id' => null,
    //                             'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
    //                             'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
    //                         ],
    //                         [
    //                             'complement' => Null,
    //                             'total' => '40.00',
    //                             'tarif' => '30.00',
    //                             'type_unite_id' => 2,
    //                             'quantite' => '0.50',
    //                             'tarif_min' => '40.00',
    //                             'tarif_pro_rata' => false,
    //                             'tarif_min_pour' => '1.00',
    //                             'tarif_min_pro_rata' => false,
    //                             'date' => '2019-01-01',
    //                             'heure' => '00:00:00',
    //                             'sapeur_id' => 2,
    //                             'compte_id' => 4,
    //                             'intervention_id' => $this->interventionId,
    //                             'exercice_id' => null,
    //                             'ecriture_categorie_id' => 4,
    //                             'cours_sapeur_id' => null,
    //                             'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
    //                             'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
    //                         ],
    //                         [
    //                             'complement' => Null,
    //                             'total' => '100.00',
    //                             'tarif' => '30.00',
    //                             'type_unite_id' => 2,
    //                             'quantite' => '3.25',
    //                             'tarif_min' => '40.00',
    //                             'tarif_pro_rata' => false,
    //                             'tarif_min_pour' => '1.00',
    //                             'tarif_min_pro_rata' => false,
    //                             'date' => '2019-01-01',
    //                             'heure' => '00:00:00',
    //                             'sapeur_id' => 3,
    //                             'compte_id' => 4,
    //                             'intervention_id' => $this->interventionId,
    //                             'exercice_id' => null,
    //                             'ecriture_categorie_id' => 4,
    //                             'cours_sapeur_id' => null,
    //                             'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
    //                             'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
    //                         ],
    //                     ]
    //                 ]
    //             ]
    //         );
    // }

    // /**
    //  * Test add exercice
    //  *
    //  * @return void
    //  * @throws Exception
    //  */
    // public function testImputationImputationTarifComplex()
    // {
    //     $param = array(
    //         "indemnite_intervention_type_id" => 2
    //     );
    //     $response = $this->json('POST', "/api/v2/imputation/intervention/$this->interventionId", $param);

    //     $response
    //         ->assertStatus(200)
    //         ->assertJson(
    //             [
    //                 "data" => [
    //                     'statut' => InterventionBusiness::INTERVENTION_STATUT_IMPUTE,
    //                     'ecritures' => [
    //                         [
    //                             'complement' => Null,
    //                             'total' => '127.50',
    //                             'tarif' => '30.00',
    //                             'type_unite_id' => 2,
    //                             'quantite' => '4.25',
    //                             'tarif_min' => null,
    //                             'tarif_min_pour' => null,
    //                             'taux' => NULL,
    //                             'taux_description' => NULL,
    //                             'date' => '2019-01-01',
    //                             'heure' => '00:00:00',
    //                             'sapeur_id' => 1,
    //                             'compte_id' => 4,
    //                             'intervention_id' => $this->interventionId,
    //                             'exercice_id' => null,
    //                             'ecriture_categorie_id' => 4,
    //                             'cours_sapeur_id' => null,
    //                             'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
    //                             'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
    //                         ],
    //                         [
    //                             'complement' => Null,
    //                             'total' => '450.00',
    //                             'tarif' => '30.00',
    //                             'type_unite_id' => 2,
    //                             'quantite' => '12.00',
    //                             'tarif_min' => null,
    //                             'tarif_min_pour' => null,
    //                             'taux' => '1.25',
    //                             'taux_description' => 'Nuit',
    //                             'date' => '2019-01-01',
    //                             'heure' => '00:00:00',
    //                             'sapeur_id' => 1,
    //                             'compte_id' => 4,
    //                             'intervention_id' => $this->interventionId,
    //                             'exercice_id' => null,
    //                             'ecriture_categorie_id' => 4,
    //                             'cours_sapeur_id' => null,
    //                             'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
    //                             'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
    //                         ],
    //                         [
    //                             'complement' => Null,
    //                             'total' => '15.00',
    //                             'tarif' => '30.00',
    //                             'type_unite_id' => 2,
    //                             'quantite' => '0.50',
    //                             'tarif_min' => null,
    //                             'tarif_min_pour' => null,
    //                             'taux' => NULL,
    //                             'taux_description' => NULL,
    //                             'date' => '2019-01-01',
    //                             'heure' => '00:00:00',
    //                             'sapeur_id' => 2,
    //                             'compte_id' => 4,
    //                             'intervention_id' => $this->interventionId,
    //                             'exercice_id' => null,
    //                             'ecriture_categorie_id' => 4,
    //                             'cours_sapeur_id' => null,
    //                             'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
    //                             'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
    //                         ],
    //                         [
    //                             'complement' => Null,
    //                             'total' => '97.50',
    //                             'tarif' => '30.00',
    //                             'type_unite_id' => 2,
    //                             'quantite' => '3.25',
    //                             'tarif_min' => null,
    //                             'tarif_min_pour' => null,
    //                             'taux' => NULL,
    //                             'taux_description' => NULL,
    //                             'date' => '2019-01-01',
    //                             'heure' => '00:00:00',
    //                             'sapeur_id' => 3,
    //                             'compte_id' => 4,
    //                             'intervention_id' => $this->interventionId,
    //                             'exercice_id' => null,
    //                             'ecriture_categorie_id' => 4,
    //                             'cours_sapeur_id' => null,
    //                             'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
    //                             'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
    //                         ],
    //                     ]
    //                 ]
    //             ]
    //         );
    // }

    public function testImputationImputationTarifTauxNuitEtWeekend()
    {
        // TODO: Créer tarif nuit/week-end
        $param = ["indemnite_intervention_type_id" => 2];

        // TODO: Créer intervention
        $intervention = Intervention::factory()->make();
        // Weekend between the 01.03.25 and 02.03.25
        $intervention->date_debut = '2025-02-27';
        $intervention->heure_debut = '12:00';
        $intervention->date_fin = '2025-03-05';
        $intervention->heure_fin = '12:00';
        $response = $this->json('POST', '/api/v2/interventions', $intervention->toArray());
        $id = $response['data']['id'];

        $sapeurs = [
            [
                // tarif standare unique
                'sapeur_id' => 1,
                'debut' => '2025-02-28 12:00',
                'fin' => '2025-02-28 13:00',
                'piquet' => 0
            ],
            [
                // Tarif nuit unique
                'sapeur_id' => 2,
                'debut' => '2025-02-28 22:00',
                'fin' => '2025-02-28 23:00',
                'piquet' => 0
            ],
            [
                // Tarif weekend unique
                'sapeur_id' => 3,
                'debut' => '2025-03-01 10:00',
                'fin' => '2025-03-01 11:00',
                'piquet' => 0
            ],
            [
                // Tarif nuit + standard
                'sapeur_id' => 4,
                'debut' => '2025-02-28 18:00',
                'fin' => '2025-02-28 21:00',
                'piquet' => 0
            ],
            [
                // Tarif nuit + standard + week-end
                'sapeur_id' => 5,
                'debut' => '2025-02-28 18:00',
                'fin' => '2025-03-02 21:00',
                'piquet' => 0
            ],
            [
                // Tarif nuit + standard + week-end + nuit + standard
                'sapeur_id' => 6,
                'debut' => '2025-02-28 18:00',
                'fin' => '2025-03-03 21:00',
                'piquet' => 0
            ],
            [
                // Tarif nuit + standard + week-end + nuit + standard + full
                'sapeur_id' => 7,
                'debut' => '2025-02-27 18:00',
                'fin' => '2025-03-04 21:00',
                'piquet' => 0
            ],
        ];

        $this->interventionService->addPresences($id, $sapeurs);
        $this->interventionService->validerInterventionById($id);

        $response = $this->json('POST', "/api/v2/imputation/intervention/$id", $param);
        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    "data" => [
                        "statut" => 3,
                        "ecritures" => [
                            [
                                "total" => "30.00",
                                "quantite" => "1.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => null,
                                "taux_description" => null,
                                "sapeur_id" => 1,
                                "exercice_comptable_id" => 4,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "37.50",
                                "quantite" => "1.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => "1.25",
                                "taux_description" => "Nuit",
                                "sapeur_id" => 2,
                                "exercice_comptable_id" => 4,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "37.50",
                                "quantite" => "1.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => "1.25",
                                "taux_description" => "Weekend",
                                "sapeur_id" => 3,
                                "exercice_comptable_id" => 4,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "60.00",
                                "quantite" => "2.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => null,
                                "taux_description" => null,
                                "sapeur_id" => 4,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "37.50",
                                "quantite" => "1.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => "1.25",
                                "taux_description" => "Nuit",
                                "sapeur_id" => 4,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "60.00",
                                "quantite" => "2.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => null,
                                "taux_description" => null,
                                "sapeur_id" => 5,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "150.00",
                                "quantite" => "4.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => "1.25",
                                "taux_description" => "Nuit",
                                "sapeur_id" => 5,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "1687.50",
                                "quantite" => "45.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => "1.25",
                                "taux_description" => "Weekend",
                                "sapeur_id" => 5,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "420.00",
                                "quantite" => "14.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => null,
                                "taux_description" => null,
                                "sapeur_id" => 6,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "487.50",
                                "quantite" => "13.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => "1.25",
                                "taux_description" => "Nuit",
                                "sapeur_id" => 6,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "1800.00",
                                "quantite" => "48.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => "1.25",
                                "taux_description" => "Weekend",
                                "sapeur_id" => 6,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "1140.00",
                                "quantite" => "38.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => null,
                                "taux_description" => null,
                                "sapeur_id" => 7,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "1387.50",
                                "quantite" => "37.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => "1.25",
                                "taux_description" => "Nuit",
                                "sapeur_id" => 7,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "1800.00",
                                "quantite" => "48.00",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => "1.25",
                                "taux_description" => "Weekend",
                                "sapeur_id" => 7,
                                "type" => 1,
                                "module" => 2,
                            ],
                        ]
                    ]
                ]
            );
    }
}
