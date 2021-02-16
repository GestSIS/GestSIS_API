<?php

namespace Test\Feature;

use App\Infrastructure\Models\AvsParam;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\SisParam;
use Tests\TestCase;

class DecompteTest extends TestCase
{
    /**
     * Test simple de création de décompte sans déductions
     */
    public function testDecompteSimple()
    {
        $ecritures = [
            array(
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 1,
                "indemnite" => 0,
                "frais" => 0,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 1,
                "ecriture_categorie_id" => 1,
            ),
            array(
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 0,
                "indemnite" => 2,
                "frais" => 0,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 1,
                "ecriture_categorie_id" => 1,
            ),
            array(
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 0,
                "indemnite" => 0,
                "frais" => 4,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 1,
                "ecriture_categorie_id" => 1,
            ),
            array(
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 1,
                "indemnite" => 0,
                "frais" => 0,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 1,
                "ecriture_categorie_id" => 1,
            ),
            array(
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 0,
                "indemnite" => 2,
                "frais" => 0,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 1,
                "ecriture_categorie_id" => 1,
            ),
            array(
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 0,
                "indemnite" => 0,
                "frais" => 4,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 1,
                "ecriture_categorie_id" => 1,
            )
        ];
        Ecriture::insert($ecritures);

        AvsParam::updateOrCreate([], [
            'taux_avs' => "0.5",
            'taux_ac' => "0.5",
            'franchise_avs' => 2300,
            'franchise_imposition' => 5000
        ]);

        $params = [
            'designation' => 'test',
            'deduction' => 0,
            'exerciceComptableId' => 1,
        ];

        $response = $this->json('POST', "api/v2/decomptes/create", $params);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'designation',
                    'exercice_comptable_id',
                    'deduction'
                ]
            ])->assertJson(
                [
                    "data" => [
                        "designation" => "Decompte n°xxx",
                        "exercice_comptable_id" => 1,
                        "deduction" => 0,
                        "avsTotal" => 0,
                        "acTotal" => 0
                    ]
                ]
            );

        $response = $this->json('GET', "api/v2/decomptes/" . $response['data']['id']);

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
                            'frais',
                            'amende',
                            'avs',
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
                                "solde" => 1.00,
                                "indemnite" => 2.00,
                                "frais" => 4.00,
                                "amende" => 0.00,
                                "avs" => 0.00,
                                "total" => 7.00
                            ],
                            [
                                "sapeur_id" => 2,
                                "solde" => 1.00,
                                "indemnite" => 2.00,
                                "frais" => 4.00,
                                "amende" => 0.00,
                                "avs" => 0.00,
                                "total" => 7.00
                            ]
                        ]
                    ]
                ]
            );

        //vérification qu'on ne paye pas deux fois
        $response = $this->json('POST', "api/v2/decomptes/create", $params);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'designation',
                    'exercice_comptable_id',
                    'deduction'
                ]
            ])->assertJson(
                [
                    "data" => [
                        "designation" => "Decompte n°xxx",
                        "exercice_comptable_id" => 1,
                        "deduction" => 0,
                        "avsTotal" => 0,
                        "acTotal" => 0
                    ]
                ]
            );

        $response = $this->json('GET', "api/v2/decomptes/" . $response['data']['id']);

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
                            'frais',
                            'amende',
                            'avs',
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
            array(
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 7400,
                "indemnite" => 0,
                "frais" => 0,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ),
            array(
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 0,
                "indemnite" => 2400,
                "frais" => 0,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ),
            array(
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 6000,
                "indemnite" => 1400,
                "frais" => 0,
                "sapeur_id" => 3,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            )
        ];
        Ecriture::insert($ecritures);

        AvsParam::updateOrCreate([], [
            'taux_avs' => "0.05275",
            'taux_ac' => "0.12",
            'franchise_avs' => 2300,
            'franchise_imposition' => 5000
        ]);

        $params = [
            'designation' => 'test',
            'deduction' => 1,
            'exerciceComptableId' => 2,
        ];
        $response = $this->json('POST', "api/v2/decomptes/create", $params);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'designation',
                    'exercice_comptable_id',
                    'deduction'
                ]
            ])->assertJson(
                [
                    "data" => [
                        "designation" => "Decompte n°xxx",
                        "exercice_comptable_id" => 2,
                        "deduction" => 1,
                        "avsTotal" => 379.79999999999995,
                        "acTotal" => 864
                    ]
                ]
            );

        $response = $this->json('GET', "api/v2/decomptes/" . $response['data']['id']);

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
                            'frais',
                            'amende',
                            'avs',
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
                                "frais" => "0.00",
                                "amende" => "0.00",
                                "avs" => "414.60",
                                "total" => "6985.40"
                            ],
                            [
                                "sapeur_id" => 2,
                                "solde" => "0.00",
                                "indemnite" => "2400.00",
                                "frais" => "0.00",
                                "amende" => "0.00",
                                "avs" => "414.60",
                                "total" => "1985.40"
                            ],
                            [
                                "sapeur_id" => 3,
                                "solde" => "6000.00",
                                "indemnite" => "1400.00",
                                "frais" => "0.00",
                                "amende" => "0.00",
                                "avs" => "414.60",
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
            array(
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 6900,
                "indemnite" => 0,
                "frais" => 0,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 3,
                "ecriture_categorie_id" => 1,
            ),
            array(
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 0,
                "indemnite" => 1900,
                "frais" => 0,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 3,
                "ecriture_categorie_id" => 1,
            ),
            array(
                "designation" => "test",
                "total" => 1,
                "tarif" => 0,
                "type_unite_id" => 1,
                "quantite" => 0,
                "solde" => 6000,
                "indemnite" => 900,
                "frais" => 0,
                "sapeur_id" => 3,
                "compte_id" => 1,
                "exercice_comptable_id" => 3,
                "ecriture_categorie_id" => 1,
            )
        ];
        Ecriture::insert($ecritures);

        AvsParam::updateOrCreate([], [
            'taux_avs' => "0.05275",
            'taux_ac' => "0.12",
            'franchise_avs' => 2300,
            'franchise_imposition' => 5000
        ]);

        $params = [
            'designation' => 'test',
            'deduction' => 1,
            'exerciceComptableId' => 3,
        ];

        $response = $this->json('POST', "api/v2/decomptes/create", $params);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'designation',
                    'exercice_comptable_id',
                    'deduction'
                ]
            ])->assertJson(
                [
                    "data" => [
                        "designation" => "Decompte n°xxx",
                        "exercice_comptable_id" => 3,
                        "deduction" => 1,
                        "avsTotal" => 0,
                        "acTotal" => 0
                    ]
                ]
            );

        $response = $this->json('GET', "api/v2/decomptes/" . $response['data']['id']);

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
                            'frais',
                            'amende',
                            'avs',
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
                                "solde" => "6900.00",
                                "indemnite" => "0.00",
                                "frais" => "0.00",
                                "amende" => "0.00",
                                "avs" => "0",
                                "total" => "6900"
                            ],
                            [
                                "sapeur_id" => 2,
                                "solde" => "0.00",
                                "indemnite" => "1900.00",
                                "frais" => "0.00",
                                "amende" => "0.00",
                                "avs" => "0",
                                "total" => "1900"
                            ],
                            [
                                "sapeur_id" => 3,
                                "solde" => "6000.00",
                                "indemnite" => "900.00",
                                "frais" => "0.00",
                                "amende" => "0.00",
                                "avs" => "0",
                                "total" => "6900"
                            ]
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
            'IBAN' => 'CH51 0022 5225 9529 1301 C',
            'bic' => 'UBSWCHZH80A'
        ]);

        $response = $this->json('POST', "api/v2/decomptes/5/iso20022", $data);

        $response->assertStatus(200);
    }
}
