<?php

namespace Test\Feature;

use App\Infrastructure\Models\SisParam;
use Tests\TestCase;

class PaiementTest extends TestCase
{

    /**
     * get  paiements.
     *
     * @return void
     */
    public function testPaiement()
    {
        $response = $this->json('GET', "api/v2/paiements/1");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
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
            ]);
    }

    /**
     * get all paiements for annee comptable.
     *
     * @return void
     */
    public function testPaiementAnneeComptable()
    {
        $response = $this->json('GET', "api/v2/paiements/exercice-comptable/4");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                "data" => [
                    '*' => [
                        "id",
                        "exercice_comptable_id",
                        "designation",
                        "deduction",
                        "avs_total",
                        "ac_total",
                        "paiements" => [
                            '*' => [
                                "id",
                                "decompte_id",
                                "sapeur_id",
                                "solde",
                                "indemnite",
                                'frais_forfaitaire',
                                'frais_effectif',
                                'autre',
                                "avs_ac",
                                "total"
                            ]
                        ]
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
        ];
        SisParam::updateOrCreate([], [
            'nom' => "SIS Delémont",
            'iban' => 'CH51 0022 5225 9529 1301 C',
            'bic' => 'UBSWCHZH80A'
        ]);

        $response = $this->json('GET', "/api/v2/paiements/3/iso20022", $data);
        $response->assertStatus(200);
    }
}
