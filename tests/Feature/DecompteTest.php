<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Ecriture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeompteTest extends TestCase
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


        $data = array();
        $data['designation'] = "test";
        $data['taux_avs'] = "0.5";
        $data['taux_ac'] = "0.5";
        $data['deduction'] = 0;
        $data['exerciceComptableId'] = "1";
        $data['minimumImposableAVSAC'] = 2300;
        $data['minimumSoldeImposable'] = 5000;
        
        $response = $this->json('POST', "api/v2/decompte/create", $data);

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
                        "designation" => "test",
                        "exercice_comptable_id" => "1",
                        "deduction" => "0",
                        "avsTotal" => 0,
                        "acTotal" => 0
                    ]
                ]
            );

        $response = $this->json('GET', "api/v2/paiement/decompte/" . $response['data']['id']);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
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
            ])->assertJson(
                [
                    "data" => [
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
            );

        //vérification qu'on ne paye pas deux fois
        $response = $this->json('POST', "api/v2/decompte/create", $data);

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
                        "designation" => "test",
                        "exercice_comptable_id" => "1",
                        "deduction" => "0",
                        "avsTotal" => 0,
                        "acTotal" => 0
                    ]
                ]
            );

        $response = $this->json('GET', "api/v2/paiement/decompte/" . $response['data']['id']);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
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


        $data = array();
        $data['designation'] = "test";
        $data['taux_avs'] = "0.05275";
        $data['taux_ac'] = "0.12";
        $data['deduction'] = 1;
        $data['exerciceComptableId'] = "2";
        $data['minimumImposableAVSAC'] = 2300;
        $data['minimumSoldeImposable'] = 5000;
        $response = $this->json('POST', "api/v2/decompte/create", $data);

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
                        "designation" => "test",
                        "exercice_comptable_id" => "2",
                        "deduction" => "1",
                        "avsTotal" => 379.79999999999995,
                        "acTotal" => 864
                    ]
                ]
            );

        $response = $this->json('GET', "api/v2/paiement/decompte/" . $response['data']['id']);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
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
            ])->assertJson(
                [
                    "data" => [
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


        $data = array();
        $data['designation'] = "test";
        $data['taux_avs'] = "0.05275";
        $data['taux_ac'] = "0.12";
        $data['deduction'] = 1;
        $data['exerciceComptableId'] = "3";
        $data['minimumImposableAVSAC'] = 2300;
        $data['minimumSoldeImposable'] = 5000;
        $response = $this->json('POST', "api/v2/decompte/create", $data);

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
                        "designation" => "test",
                        "exercice_comptable_id" => "3",
                        "deduction" => "1",
                        "avsTotal" => 0,
                        "acTotal" => 0
                    ]
                ]
            );

        $response = $this->json('GET', "api/v2/paiement/decompte/" . $response['data']['id']);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
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
            ])->assertJson(
                [
                    "data" => [
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
            );
    }

    /**
     * get all décomptes.
     *
     * @return void
     */
    public function testDecompteAll()
    {
        $response = $this->json('GET', "api/v2/decompte/");

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
     * get all décomptes.
     *
     * @return void
     */
    public function testDecompteAnneeComptable()
    {
        $response = $this->json('GET', "api/v2/decompte/exercice-comptable/3");

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
        $response = $this->json('GET', "api/v2/decompte/1");

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
            'nom' => "SIS Delémont",
            'IBAN' => 'CH51 0022 5225 9529 1301 C',
            'bic' => 'UBSWCHZH80A'
        ];
        $response = $this->json('POST', "api/v2/decompte/5/iso20022", $data);

        $response->assertStatus(200);
    }
}
