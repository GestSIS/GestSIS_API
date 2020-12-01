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
        $response = $this->json('GET', "api/v2/paiement/exerciceComptable/4");

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
}
