<?php

namespace Test\Feature;

use App\Domaine\Business\PaiementBusiness;
use App\Infrastructure\Models\AvsParam;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\SisParam;
use Carbon\Carbon;
use Tests\TestCase;

class DecompteTest extends TestCase
{
    /**
     * Test simple de création de décompte sans déductions
     */
    public function testDecompteSimple()
    {
        $ecritures = [
            [
                "designation" => "test",
                "total" => 1,
                "tarif" => 1,
                "type_unite_id" => 1,
                "quantite" => 1,
                "module" => 0,
                "type" => 1,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 1,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 1,
                "tarif" => 2,
                "type_unite_id" => 1,
                "quantite" => 1,
                "module" => 0,
                "type" => 2,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 1,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 1,
                "tarif" => 4,
                "type_unite_id" => 1,
                "quantite" => 1,
                "module" => 0,
                "type" => 3,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 1,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 1,
                "tarif" => 1,
                "type_unite_id" => 1,
                "quantite" => 1,
                "module" => 0,
                "type" => 1,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 1,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 1,
                "tarif" => 2,
                "type_unite_id" => 1,
                "quantite" => 1,
                "module" => 0,
                "type" => 2,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 1,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 1,
                "tarif" => 4,
                "type_unite_id" => 1,
                "quantite" => 1,
                "module" => 0,
                "type" => 3,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 1,
                "ecriture_categorie_id" => 1,
            ]
        ];
        Ecriture::insert($ecritures);
        $ecritures = Ecriture::where('exercice_comptable_id', 1)->get();

        AvsParam::updateOrCreate([], [
            'taux_avs' => "1.0",
            'taux_ac' => "1.0",
            'franchise_avs' => 2300,
            'franchise_imposition' => 5000
        ]);

        $params = [
            'designation' => 'test',
            'deduction' => 0,
            'exercice_comptable_id' => 1,
            'date' => Carbon::today()
        ];

        $business = new PaiementBusiness();
        $response = $business->creerDecompte($ecritures, $params['designation'], $params['exercice_comptable_id'], $params['date'], $params['deduction']);
        // $response = $this->json('POST', "api/v2/decomptes/creer-annuel", $params);

        // $response
        //     ->assertStatus(200)
        //     ->assertJsonStructure([
        //         'data' => [
        //             'id',
        //             'designation',
        //             'exercice_comptable_id',
        //             'deduction'
        //         ]
        //     ])->assertJson(
        //         [
        //             "data" => [
        //                 "designation" => "Decompte n°xxx",
        //                 "exercice_comptable_id" => 1,
        //                 "deduction" => 0,
        //                 "avs_total" => 0,
        //                 "ac_total" => 0
        //             ]
        //         ]
        //     );
        $response = $this->json('GET', "api/v2/decomptes/" . $response['id']);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'designation',
                    'paiements' => [
                        '*' =>
                        [
                            'id',
                            'decompte_id',
                            'solde',
                            'indemnite',
                            'frais_forfaitaire',
                            'frais_effectif',
                            'autre',
                            'avs_ac',
                            'total',
                            'sapeur_id',
                        ]
                    ]
                ]
            ])->assertJson(
                [
                    "data" => [
                        'paiements' => [
                            [
                                "sapeur_id" => 1,
                                "solde" => "1.00",
                                "indemnite" => "2.00",
                                "frais_forfaitaire" => "4.00",
                                "frais_effectif" => "0.00",
                                "autre" => "0.00",
                                "avs_ac" => "0.00",
                                "total" => "7.00"
                            ],
                            [
                                "sapeur_id" => 2,
                                "solde" => "1.00",
                                "indemnite" => "2.00",
                                "frais_forfaitaire" => "4.00",
                                "frais_effectif" => "4.00",
                                "autre" => "0.00",
                                "avs_ac" => "0.00",
                                "total" => "7.00"
                            ]
                        ]
                    ]
                ]
            );

        //vérification qu'on ne paye pas deux fois
        $response = $business->creerDecompte($ecritures, $params['designation'], $params['exercice_comptable_id'], $params['date'], $params['deduction']);
        // $response = $this->json('POST', "api/v2/decomptes/create", $params);

        // $response
        //     ->assertStatus(200)
        //     ->assertJsonStructure([
        //         'data' => [
        //             'id',
        //             'designation',
        //             'exercice_comptable_id',
        //             'deduction'
        //         ]
        //     ])->assertJson(
        //         [
        //             "data" => [
        //                 "designation" => "Decompte n°xxx",
        //                 "exercice_comptable_id" => 1,
        //                 "deduction" => 0,
        //                 "avs_total" => 0,
        //                 "ac_total" => 0
        //             ]
        //         ]
        //     );

        $response = $this->json('GET', "api/v2/decomptes/" . $response['id']);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'paiements' => [
                        '*' => [
                            'id',
                            'decompte_id',
                            'solde',
                            'indemnite',
                            'frais_forfaitaire',
                            'frais_effectif',
                            'autre',
                            'avs_ac',
                            'total',
                            'sapeur_id',
                        ]
                    ]
                ]
            ])->assertJson(
                [
                    "data" => []
                ]
            );
    }

    public function testDecompteDeduction()
    {
        $ecritures = [
            [
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 7400,
                "indemnite" => 0,
                "frais_forfaitaire" => 0,
                "frais_effectif" => 0,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 0,
                "indemnite" => 2400,
                "frais_forfaitaire" => 0,
                "frais_effectif" => 0,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 6000,
                "indemnite" => 1400,
                "frais_forfaitaire" => 0,
                "frais_effectif" => 0,
                "sapeur_id" => 3,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ]
        ];
        Ecriture::insert($ecritures);
        $ecritures = Ecriture::where('exercice_comptable_id', 2)->get();

        AvsParam::updateOrCreate([], [
            'taux_avs' => "0.1055",
            'taux_ac' => "0.24",
            'franchise_avs' => 2300,
            'franchise_imposition' => 5000
        ]);

        $params = [
            'designation' => 'test',
            'deduction' => 1,
            'exercice_comptable_id' => 2,
            'date' => Carbon::today(),
        ];

        $business = new PaiementBusiness();
        $response = $business->creerDecompte($ecritures, $params['designation'], $params['exercice_comptable_id'], $params['date'], $params['deduction']);
        // $response = $this->json('POST', "api/v2/decomptes/creer-annuel", $params);

        // $response
        //     ->assertStatus(200)
        //     ->assertJsonStructure([
        //         'data' => [
        //             'id',
        //             'designation',
        //             'exercice_comptable_id',
        //             'deduction'
        //         ]
        //     ])->assertJson(
        //         [
        //             "data" => [
        //                 "designation" => "Decompte n°xxx",
        //                 "exercice_comptable_id" => 2,
        //                 "deduction" => 1,
        //                 "avs_total" => 379.79999999999995,
        //                 "ac_total" => 864
        //             ]
        //         ]
        //     );

        $response = $this->json('GET', "api/v2/decomptes/" . $response['id']);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'paiements' => [
                        '*' => [
                            'id',
                            'decompte_id',
                            'solde',
                            'indemnite',
                            'frais_forfaitaire',
                            'frais_effectif',
                            'autre',
                            'avs_ac',
                            'total',
                            'sapeur_id',
                        ]
                    ]
                ]
            ])->assertJson(
                [
                    "data" => [
                        'paiements' => [
                            [
                                "sapeur_id" => 1,
                                "solde" => "7400.00",
                                "indemnite" => "0.00",
                                "frais_forfaitaire" => "0.00",
                                "frais_effectif" => "0.00",
                                "autre" => "0.00",
                                "avs_ac" => "414.60",
                                "total" => "6985.40"
                            ],
                            [
                                "sapeur_id" => 2,
                                "solde" => "0.00",
                                "indemnite" => "2400.00",
                                "frais_forfaitaire" => "0.00",
                                "frais_effectif" => "0.00",
                                "autre" => "0.00",
                                "avs_ac" => "414.60",
                                "total" => "1985.40"
                            ],
                            [
                                "sapeur_id" => 3,
                                "solde" => "6000.00",
                                "indemnite" => "1400.00",
                                "frais_forfaitaire" => "0.00",
                                "frais_effectif" => "0.00",
                                "autre" => "0.00",
                                "avs_ac" => "414.60",
                                "total" => "6985.40"
                            ]
                        ]
                    ]
                ]
            );
    }


    public function testDeuxDecompteDeduction()
    {
        $ecritures = [
            [
                "designation" => "test",
                "total" => 6000,
                "type_unite_id" => 1,
                "quantite" => 0,
                "tarif" => 6000,
                "module" => 0,
                "type" => 1,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 3,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 900,
                "type_unite_id" => 1,
                "quantite" => 0,
                "tarif" => 900,
                "module" => 0,
                "type" => 2,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 3,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "type_unite_id" => 1,
                "total" => 1900,
                "quantite" => 1,
                "tarif" => 1900,
                "module" => 0,
                "type" => 2,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 3,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 6000,
                "type_unite_id" => 1,
                "quantite" => 0,
                "tarif" => 6000,
                "module" => 0,
                "type" => 1,
                "sapeur_id" => 3,
                "compte_id" => 1,
                "exercice_comptable_id" => 3,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 900,
                "type_unite_id" => 1,
                "quantite" => 0,
                "tarif" => 900,
                "module" => 0,
                "type" => 2,
                "sapeur_id" => 3,
                "compte_id" => 1,
                "exercice_comptable_id" => 3,
                "ecriture_categorie_id" => 1,
            ],
        ];
        Ecriture::insert($ecritures);
        $ecritures = Ecriture::where('exercice_comptable_id', 3)->get();

        AvsParam::updateOrCreate([], [
            'taux_avs' => "0.1055",
            'taux_ac' => "0.24",
            'franchise_avs' => 2300,
            'franchise_imposition' => 5000
        ]);

        $params = [
            'designation' => 'test',
            'deduction' => 1,
            'exercice_comptable_id' => 3,
            'date' => Carbon::today(),
        ];

        $business = new PaiementBusiness();
        $response = $business->creerDecompte($ecritures, $params['designation'], $params['exercice_comptable_id'], $params['date'], $params['deduction']);
        // $response = $this->json('POST', "api/v2/decomptes/creer-annuel", $params);

        // $response
        //     ->assertStatus(200)
        //     ->assertJsonStructure([
        //         'data' => [
        //             'id',
        //             'designation',
        //             'exercice_comptable_id',
        //             'deduction'
        //         ]
        //     ])->assertJson(
        //         [
        //             "data" => [
        //                 "designation" => "Decompte n°xxx",
        //                 "exercice_comptable_id" => 3,
        //                 "deduction" => 1,
        //                 "avs_total" => 0,
        //                 "ac_total" => 0
        //             ]
        //         ]
        //     );

        $response = $this->json('GET', "api/v2/decomptes/" . $response['id']);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'paiements' => [
                        '*' => [
                            'id',
                            'decompte_id',
                            'solde',
                            'indemnite',
                            'frais_forfaitaire',
                            'frais_effectif',
                            'autre',
                            'avs_ac',
                            'total',
                            'sapeur_id',
                        ]
                    ]
                ]
            ])->assertJson(
                [
                    "data" => [
                        'paiements' => [
                            [
                                "sapeur_id" => 1,
                                "solde" => "6000.00",
                                "indemnite" => "900.00",
                                "frais_forfaitaire" => "0.00",
                                "frais_effectif" => "0.00",
                                "autre" => "0.00",
                                "avs_ac" => "0",
                                "total" => "6900"
                            ],
                            [
                                "sapeur_id" => 2,
                                "solde" => "0.00",
                                "indemnite" => "1900.00",
                                "frais_forfaitaire" => "0.00",
                                "frais_effectif" => "0.00",
                                "autre" => "0.00",
                                "avs_ac" => "0",
                                "total" => "1900"
                            ],
                            [
                                "sapeur_id" => 3,
                                "solde" => "6000.00",
                                "indemnite" => "900.00",
                                "frais_forfaitaire" => "0.00",
                                "frais_effectif" => "0.00",
                                "autre" => "0.00",
                                "avs_ac" => "0",
                                "total" => "6900"
                            ],
                        ]
                    ]
                ]
            );
    }

    /**
     * get all décomptes.
     *
     * @return void
     */
    public function testDecompteAnneeComptable()
    {
        $response = $this->json('GET', "api/v2/decomptes/exercice-comptable/3");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'designation',
                        'exercice_comptable_id',
                        'deduction'
                    ]
                ]
            ]);
    }

    /**
     * get un décompte.
     *
     * @return void
     */
    public function testDecompte()
    {
        $response = $this->json('GET', "api/v2/decomptes/1");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'designation',
                    'exercice_comptable_id',
                    'deduction'
                ]
            ]);
    }

    /**
     * get iso20022
     *
     * @return void
     */
    public function testIso20022()
    {
        $data = [
            'decompteId' => 5,
        ];
        SisParam::updateOrCreate([], [
            'nom' => "SIS Delémont",
            'iban' => 'CH51 0022 5225 9529 1301 C',
            'bic' => 'UBSWCHZH80A'
        ]);

        $response = $this->json('GET', "api/v2/decomptes/5/iso20022", $data);

        $response->assertStatus(200);
    }
}
