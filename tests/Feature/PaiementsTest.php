<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaiementsTest extends TestCase
{

    /**
     * get all paiements.
     *
     * @return void
     */
    public function testPaiementAll()
    {
        $response = $this->json('GET', "api/v2/paiement/");

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
            ]);
    }

    /**
     * get  paiements.
     *
     * @return void
     */
    public function testPaiement()
    {
        $response = $this->json('GET', "api/v2/paiement/1");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
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
            ]);
    }

    /**
     * get all paiements for annee comptable.
     *
     * @return void
     */
    public function testPaiementAnnee()
    {
        $response = $this->json('GET', "api/v2/paiement/exercice-comptable/4");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                "data" => [
                    '*' => [
                        "id",
                        "exercice_comptable_id",
                        "designation",
                        "deduction",
                        "avsTotal",
                        "acTotal",
                        "paiements" => [
                            '*' => [
                                "id",
                                "decompte_id",
                                "sapeur_id",
                                "solde",
                                "indemnite",
                                "frais",
                                "amende",
                                "avs",
                                "total"
                            ]
                        ]
                    ]
                ]
            ]);
    }

    /**
     * get all paiements for decompte.
     *
     * @return void
     */
    public function testPaiementDecompte()
    {
        $response = $this->json('GET', "api/v2/paiement/decompte/1");

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
            'paiementId' => 3,
            'nom' => "SIS Delémont",
            'IBAN' => 'CH51 0022 5225 9529 1301 C',
            'bic' => 'UBSWCHZH80A'
        ];

        $response = $this->json('POST', "/api/v2/paiement/3/iso20022", $data);

        $response->assertStatus(200);
    }
}
