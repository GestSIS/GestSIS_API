<?php

namespace Tests\Feature;

use App\Domaine\Business\PaiementBusiness;
use App\Domaine\Exceptions\InvalidActionException;
use App\Models\SisParam;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaiementTest extends TestCase
{
    use DatabaseTransactions;

    private function createDecompteWithPaiement(): array
    {
        $decompteId = DB::table('decomptes')->insertGetId([
            'designation' => 'test paiement',
            'date' => '2018-01-31',
            'exercice_comptable_id' => 2,
            'deduction' => 0,
            'avs_total' => 0,
            'ac_total' => 0,
            'total' => 100,
        ]);

        $paiementId = DB::table('paiements')->insertGetId([
            'decompte_id' => $decompteId,
            'sapeur_id' => 1,
            'solde' => 100,
            'indemnite' => 0,
            'frais_forfaitaire' => 0,
            'frais_effectif' => 0,
            'autre' => 0,
            'avs_ac' => 0,
            'total' => 100,
        ]);

        return ['decompte_id' => $decompteId, 'paiement_id' => $paiementId];
    }

    /**
     * get paiements.
     *
     * @return void
     */
    public function testPaiement()
    {
        $ids = $this->createDecompteWithPaiement();

        $response = $this->json('GET', "api/v2/paiements/{$ids['paiement_id']}");

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
        $ids = $this->createDecompteWithPaiement();

        SisParam::updateOrCreate([], [
            'nom' => "SIS Delémont",
            'iban' => 'CH51 0022 5225 9529 1301 C',
            'bic' => 'UBSWCHZH80A'
        ]);

        $response = $this->json('GET', "/api/v2/paiements/{$ids['paiement_id']}/iso20022");
        $response->assertStatus(200);
    }

    /**
     * Le montant iso20022 d'un paiement est arrondi aux 5 centimes
     */
    public function testIso20022ArrondiCinqCentimes()
    {
        $decompteId = DB::table('decomptes')->insertGetId([
            'designation' => 'iso20022 arrondi paiement',
            'date' => '2025-01-31',
            'exercice_comptable_id' => 2,
            'deduction' => 0,
            'avs_total' => 0,
            'ac_total' => 0,
            'total' => 19.97,
        ]);
        $paiementId = DB::table('paiements')->insertGetId([
            'decompte_id' => $decompteId,
            'sapeur_id' => 1,
            'solde' => 19.97,
            'indemnite' => 0,
            'frais_forfaitaire' => 0,
            'frais_effectif' => 0,
            'autre' => 0,
            'avs_ac' => 0,
            'total' => 19.97,
        ]);

        $xml = PaiementBusiness::iso20022PourPaiement($paiementId, 'SIS Test', 'UBSWCHZH80A', 'CH51 0022 5225 9529 1301 C');

        $this->assertStringContainsString('19.95', $xml);
        $this->assertStringNotContainsString('19.96', $xml);
        $this->assertStringNotContainsString('19.97', $xml);
    }

    /**
     * iso20022 pour un paiement à montant nul ou négatif doit lever une exception claire
     */
    public function testIso20022PaiementTotalNegatif()
    {
        $decompteId = DB::table('decomptes')->insertGetId([
            'designation' => 'iso20022 paiement négatif',
            'date' => '2025-01-31',
            'exercice_comptable_id' => 2,
            'deduction' => 0,
            'avs_total' => 0,
            'ac_total' => 0,
            'total' => -50,
        ]);
        $paiementId = DB::table('paiements')->insertGetId([
            'decompte_id' => $decompteId,
            'sapeur_id' => 1,
            'solde' => -50,
            'indemnite' => 0,
            'frais_forfaitaire' => 0,
            'frais_effectif' => 0,
            'autre' => 0,
            'avs_ac' => 0,
            'total' => -50,
        ]);

        $this->expectException(InvalidActionException::class);

        PaiementBusiness::iso20022PourPaiement($paiementId, 'SIS Test', 'UBSWCHZH80A', 'CH51 0022 5225 9529 1301 C');
    }
}
