<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Ecriture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeompteTest extends TestCase
{
    /**
     * création d'un décompte.
     *
     * @return void
     */
    public function testDecompteCreation()
    {
        $data = array();
        $data['designation'] = "test";
        $data['taux_avs'] = "0.04";
        $data['taux_ac'] = "0.02";
        $data['deduction'] = "1";
        $data['exerciceComptableId'] = "4";
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
            ]);
    }

    /**
     * Test simple de création de décompte sans déductions
     */
    public function testDecompte1()
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
                "amende_montant" => 0,
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
                "amende_montant" => 0,
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
                "amende_montant" => 0,
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
                "amende_montant" => 0,
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
                "amende_montant" => 0,
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
                "amende_montant" => 0,
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
        $response = $this->json('GET', "api/v2/decompte/exerciceComptable/3");

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
}
