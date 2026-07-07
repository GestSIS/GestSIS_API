<?php

namespace Tests\Feature;

use App\Domaine\Business\ImputationBusiness;
use App\Domaine\Business\PaiementBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\AvsParam;
use App\Models\Ecriture;
use App\Models\SisParam;
use Carbon\Carbon;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DecompteTest extends TestCase
{
    use DatabaseTransactions;

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
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 2,
                "tarif" => 2,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_INDEMNITE,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 4,
                "tarif" => 4,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_FRAIS_FORFAITAIRE,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 1,
                "tarif" => 1,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 2,
                "tarif" => 2,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_INDEMNITE,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 4,
                "tarif" => 4,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_FRAIS_FORFAITAIRE,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 4,
                "tarif" => 4,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_FRAIS_EFFECTIF,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ]
        ];
        Ecriture::insert($ecritures);
        $ecritures = Ecriture::where('exercice_comptable_id', 2)->get();

        AvsParam::updateOrCreate([], [
            'taux_avs' => "1.0",
            'taux_ac' => "1.0",
            'franchise_avs' => 2300,
            'franchise_imposition' => 5000,
            'franchise_imposition_cantonale' => 8000,
        ]);

        $params = [
            'designation' => 'test',
            'deduction' => 0,
            'exercice_comptable_id' => 2,
            'date' => Carbon::today()
        ];


        $response = PaiementBusiness::creerDecompte($ecritures, $params['designation'], $params['exercice_comptable_id'], $params['date'], $params['deduction']);

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
                                "total" => "11.00"
                            ]
                        ]
                    ]
                ]
            );

        //vérification qu'on ne paye pas deux fois
        $response = PaiementBusiness::creerDecompte($ecritures, $params['designation'], $params['exercice_comptable_id'], $params['date'], $params['deduction']);

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
                "total" => 7400,
                "tarif" => 7400,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 2400,
                "tarif" => 2400,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_INDEMNITE,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 6000,
                "tarif" => 6000,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "sapeur_id" => 3,
                "compte_id" => 1,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 1400,
                "tarif" => 1400,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "sapeur_id" => 3,
                "compte_id" => 1,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_INDEMNITE,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
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
            'franchise_imposition' => 5000,
            'franchise_imposition_cantonale' => 8000,
        ]);

        $params = [
            'designation' => 'test',
            'deduction' => 1,
            'exercice_comptable_id' => 2,
            'date' => Carbon::today(),
        ];

        $response = PaiementBusiness::creerDecompte($ecritures, $params['designation'], $params['exercice_comptable_id'], $params['date'], $params['deduction']);
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
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "tarif" => 6000,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 3,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 900,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "tarif" => 900,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_INDEMNITE,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 3,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "total" => 1900,
                "quantite" => 1,
                "tarif" => 1900,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_INDEMNITE,
                "sapeur_id" => 2,
                "compte_id" => 1,
                "exercice_comptable_id" => 3,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 6000,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "tarif" => 6000,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                "sapeur_id" => 3,
                "compte_id" => 1,
                "exercice_comptable_id" => 3,
                "ecriture_categorie_id" => 1,
            ],
            [
                "designation" => "test",
                "total" => 900,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "tarif" => 900,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_INDEMNITE,
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
            'franchise_imposition' => 5000,
            'franchise_imposition_cantonale' => 8000,
        ]);

        $params = [
            'designation' => 'test',
            'deduction' => 1,
            'exercice_comptable_id' => 3,
            'date' => Carbon::today(),
        ];

        $response = PaiementBusiness::creerDecompte($ecritures, $params['designation'], $params['exercice_comptable_id'], $params['date'], $params['deduction']);
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
        $decompteId = DB::table('decomptes')->insertGetId([
            'designation' => 'test decompte',
            'date' => '2018-01-31',
            'exercice_comptable_id' => 2,
            'deduction' => 0,
            'avs_total' => 0,
            'ac_total' => 0,
            'total' => 0,
        ]);

        $response = $this->json('GET', "api/v2/decomptes/{$decompteId}");

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
     * Un sapeur avec uniquement des frais effectifs doit recevoir un paiement
     */
    public function testDecompteFraisEffectifSeul()
    {
        Ecriture::insert([
            [
                "designation" => "test frais effectif",
                "total" => 12.50,
                "tarif" => 12.50,
                "type_unite_id" => ImputationBusiness::UNITE_PIECE,
                "quantite" => 1,
                "module" => ImputationBusiness::ECRITURE_MODULE_DIVERS,
                "type" => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_FRAIS_EFFECTIF,
                "sapeur_id" => 1,
                "compte_id" => 1,
                "exercice_comptable_id" => 2,
                "ecriture_categorie_id" => 1,
            ],
        ]);
        $ecritures = Ecriture::where('exercice_comptable_id', 2)->get();

        AvsParam::updateOrCreate([], [
            'taux_avs' => "1.0",
            'taux_ac' => "1.0",
            'franchise_avs' => 2300,
            'franchise_imposition' => 5000,
            'franchise_imposition_cantonale' => 8000,
        ]);

        $decompte = PaiementBusiness::creerDecompte($ecritures, 'test frais effectif', 2, Carbon::today(), 0);

        $response = $this->json('GET', "api/v2/decomptes/" . $decompte['id']);

        $response
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    'a_payer_total' => "12.50",
                    'paiements' => [
                        [
                            "sapeur_id" => 1,
                            "solde" => "0.00",
                            "indemnite" => "0.00",
                            "frais_forfaitaire" => "0.00",
                            "frais_effectif" => "12.50",
                            "autre" => "0.00",
                            "avs_ac" => "0.00",
                            "total" => "12.50",
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Les montants iso20022 sont arrondis aux 5 centimes (pas de troncature du centime)
     */
    public function testIso20022ArrondiCinqCentimes()
    {
        $decompteId = DB::table('decomptes')->insertGetId([
            'designation' => 'iso20022 arrondi',
            'date' => '2025-01-31',
            'exercice_comptable_id' => 2,
            'deduction' => 0,
            'avs_total' => 0,
            'ac_total' => 0,
            'total' => 19.97,
        ]);
        DB::table('paiements')->insert([
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

        $xml = PaiementBusiness::iso20022PourDecompte($decompteId, 'SIS Test', 'UBSWCHZH80A', 'CH51 0022 5225 9529 1301 C');

        $this->assertStringContainsString('19.95', $xml);
        $this->assertStringNotContainsString('19.96', $xml);
        $this->assertStringNotContainsString('19.97', $xml);
    }

    /**
     * Un IBAN de sapeur invalide doit lever une ArrayException explicite
     */
    public function testIso20022IbanSapeurInvalide()
    {
        DB::table('sapeurs')->where('id', 1)->update(['iban' => 'CH00INVALIDE']);

        $decompteId = DB::table('decomptes')->insertGetId([
            'designation' => 'iso20022 iban invalide',
            'date' => '2025-01-31',
            'exercice_comptable_id' => 2,
            'deduction' => 0,
            'avs_total' => 0,
            'ac_total' => 0,
            'total' => 10,
        ]);
        DB::table('paiements')->insert([
            'decompte_id' => $decompteId,
            'sapeur_id' => 1,
            'solde' => 10,
            'indemnite' => 0,
            'frais_forfaitaire' => 0,
            'frais_effectif' => 0,
            'autre' => 0,
            'avs_ac' => 0,
            'total' => 10,
        ]);

        $this->expectException(ArrayException::class);
        $this->expectExceptionMessage('invalides');

        PaiementBusiness::iso20022PourDecompte($decompteId, 'SIS Test', 'UBSWCHZH80A', 'CH51 0022 5225 9529 1301 C');
    }

    /**
     * Les totaux d'un sapeur sont trouvés même avec des ids reçus en string (paramètres de route)
     */
    public function testTotauxPaiementsSapeurAvecIdsString()
    {
        $exerciceComptableId = DB::table('exercice_comptables')->insertGetId([
            'annee' => 2099,
            'designation' => 'test certificat',
            'debut' => '2099-01-01',
            'fin' => '2099-12-31',
            'boucle' => 0,
        ]);
        $decompteId = DB::table('decomptes')->insertGetId([
            'designation' => 'test certificat',
            'date' => '2099-01-31',
            'exercice_comptable_id' => $exerciceComptableId,
            'deduction' => 0,
            'avs_total' => 0,
            'ac_total' => 0,
            'total' => 145,
        ]);
        DB::table('paiements')->insert([
            'decompte_id' => $decompteId,
            'sapeur_id' => 1,
            'solde' => 100,
            'indemnite' => 30,
            'frais_forfaitaire' => 0,
            'frais_effectif' => 20,
            'autre' => 0,
            'avs_ac' => 5,
            'total' => 145,
        ]);

        $total = PaiementBusiness::totauxPaiementsSapeur("$exerciceComptableId", "1");

        $this->assertEquals(100.0, $total['solde']);
        $this->assertEquals(30.0, $total['indemnite']);
        $this->assertEquals(5.0, $total['avs_ac']);
        $this->assertEquals(20.0, $total['frais_effectif']);
        $this->assertEquals(0.0, $total['frais_forfaitaire']);
    }

    /**
     * Une erreur durant la génération des certificats doit être signalée, pas avalée
     */
    public function testCertificatSalaireSansDecompteRetourneErreur()
    {
        AvsParam::updateOrCreate([], [
            'taux_avs' => "1.0",
            'taux_ac' => "1.0",
            'franchise_avs' => 2300,
            'franchise_imposition' => 5000,
            'franchise_imposition_cantonale' => 8000,
        ]);

        $response = $this->json('GET', "api/v2/exercices-comptable/99999/certificat-salaire");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['error' => ['message']]);
    }

    /**
     * get iso20022
     *
     * @return void
     */
    public function testIso20022()
    {
        $decompteId = DB::table('decomptes')->insertGetId([
            'designation' => 'iso20022 test',
            'date' => '2025-01-31',
            'exercice_comptable_id' => 2,
            'deduction' => 0,
            'avs_total' => 0,
            'ac_total' => 0,
            'total' => 0,
        ]);

        SisParam::updateOrCreate([], [
            'nom' => "SIS Delémont",
            'iban' => 'CH51 0022 5225 9529 1301 C',
            'bic' => 'UBSWCHZH80A'
        ]);

        $response = $this->json('GET', "api/v2/decomptes/{$decompteId}/iso20022");
        $response->assertStatus(200);
    }
}
