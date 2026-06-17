<?php

namespace Tests\Feature;

use App\Models\IndemniteInterventionType;
use App\Models\Intervention;
use App\Models\Sapeur;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Domaine\Business\ImputationBusiness;
use App\Domaine\Business\InterventionBusiness;
use Carbon\Carbon;

class ImputationInterventionTest extends TestCase
{
    use DatabaseTransactions;

    protected $sapeurOneId;
    protected $sapeurTwoId;
    protected $sapeurThreeId;
    protected $interventionId;

    protected int $indemniteTypeSimpleId;
    protected int $indemniteTypeComplexId;
    protected int $indemniteTypeProRataId;

    protected function setUp(): void
    {
        parent::setUp();



        // Création des types d'indemnités propres au test
        $this->indemniteTypeSimpleId = IndemniteInterventionType::create([
            'designation' => 'Type Simple',
            'tarif' => 30,
            'tarif_min' => 40,
            'tarif_min_pour' => 1,
            'taux_weekend' => null,
            'taux_nuit' => null,
            'debut' => null,
            'fin' => null,
            'compte_id' => 4,
            'phase_id' => 1,
            'type_unite_id' => 2,
            'ecriture_categorie_id' => 4,
            'par_fonction' => false,
            'type' => 1,
            'tarif_pro_rata' => false,
            'tarif_min_pro_rata' => false,
        ])->id;

        $this->indemniteTypeComplexId = IndemniteInterventionType::create([
            'designation' => 'Type Complexe (taux nuit/weekend)',
            'tarif' => 30,
            'tarif_min' => null,
            'tarif_min_pour' => null,
            'taux_weekend' => 1.25,
            'taux_nuit' => 1.25,
            'debut' => '20:00',
            'fin' => '08:00',
            'compte_id' => 4,
            'phase_id' => null,
            'type_unite_id' => 2,
            'ecriture_categorie_id' => 4,
            'par_fonction' => false,
            'type' => 1,
            'tarif_pro_rata' => true,
            'tarif_min_pro_rata' => false,
        ])->id;

        $this->indemniteTypeProRataId = IndemniteInterventionType::create([
            'designation' => 'Type Pro-Rata',
            'tarif' => 30,
            'tarif_min' => 40,
            'tarif_min_pour' => 1,
            'taux_weekend' => null,
            'taux_nuit' => null,
            'debut' => null,
            'fin' => null,
            'compte_id' => 4,
            'phase_id' => 1,
            'type_unite_id' => 2,
            'ecriture_categorie_id' => 4,
            'par_fonction' => false,
            'type' => 1,
            'tarif_pro_rata' => true,
            'tarif_min_pro_rata' => true,
        ])->id;

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['type'] = 0;
        $this->sapeurOneId = $this->json('POST', '/api/v2/sapeurs', $data)->json('data.id');

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['type'] = 0;
        $this->sapeurTwoId = $this->json('POST', '/api/v2/sapeurs', $data)->json('data.id');

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['type'] = 0;
        $this->sapeurThreeId = $this->json('POST', '/api/v2/sapeurs', $data)->json('data.id');

        $intervention = Intervention::factory()->make();
        $interventionData = $intervention->toArray();
        $interventionData['exercice_comptable_id'] = 1;
        $interventionData['date_debut'] = '2019-01-01';
        $interventionData['heure_debut'] = '00:00';
        $interventionData['date_fin'] = '2019-01-03';
        $interventionData['heure_fin'] = '00:00';

        $this->interventionId = $this->json('POST', '/api/v2/interventions', $interventionData)->json('data.id');

        $sapeurs = array(
            array(
                'sapeur_id' => $this->sapeurOneId,
                'debut' => '2019-01-01 20:00',
                'fin' => '2019-01-02 12:15',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => $this->sapeurTwoId,
                'debut' => '2019-01-01 12:00',
                'fin' => '2019-01-01 12:30',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => $this->sapeurThreeId,
                'debut' => '2019-01-01 12:15',
                'fin' => '2019-01-01 15:30',
                'piquet' => 0
            ),
        );

        $this->json('POST', "/api/v2/interventions/{$this->interventionId}/sapeurs", ['sapeurs' => $sapeurs]);
        $this->json('POST', "/api/v2/interventions/{$this->interventionId}/valider");
    }

    /**
     * Test imputation avec tarif minimum simple sans pro-rata
     * 
     * Vérifie que le tarif minimum de 40 CHF pour 1h est appliqué,
     * puis le tarif normal de 30 CHF/h pour les heures supplémentaires.
     *
     * @return void
     * @throws Exception
     */
    public function testImputationImputationTarifSimpleSansProRata()
    {
        $param = array(
            "indemnite_intervention_type_id" => $this->indemniteTypeSimpleId
        );
        $response = $this->json('POST', "/api/v2/imputation/intervention/$this->interventionId", $param);

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.ecritures')
            ->assertJson(
                [
                    "data" => [
                        'statut' => InterventionBusiness::INTERVENTION_STATUT_IMPUTE,
                        'ecritures' => [
                            [
                                'complement' => Null,
                                'total' => '490.00',
                                'tarif' => '30.00',
                                'type_unite_id' => 2,
                                'quantite' => '16.25',
                                'tarif_min' => '40.00',
                                'tarif_pro_rata' => false,
                                'tarif_min_pour' => '1.00',
                                'tarif_min_pro_rata' => false,
                                'date' => '2019-01-01',
                                'heure' => '00:00:00',
                                'sapeur_id' => $this->sapeurOneId,
                                'compte_id' => 4,
                                'intervention_id' => $this->interventionId,
                                'exercice_id' => null,
                                'ecriture_categorie_id' => 4,
                                'cours_sapeur_id' => null,
                                'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                                'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
                            ],
                            [
                                'complement' => Null,
                                'total' => '40.00',
                                'tarif' => '30.00',
                                'type_unite_id' => 2,
                                'quantite' => '0.50',
                                'tarif_min' => '40.00',
                                'tarif_pro_rata' => false,
                                'tarif_min_pour' => '1.00',
                                'tarif_min_pro_rata' => false,
                                'date' => '2019-01-01',
                                'heure' => '00:00:00',
                                'sapeur_id' => $this->sapeurTwoId,
                                'compte_id' => 4,
                                'intervention_id' => $this->interventionId,
                                'exercice_id' => null,
                                'ecriture_categorie_id' => 4,
                                'cours_sapeur_id' => null,
                                'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                                'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
                            ],
                            [
                                'complement' => Null,
                                'total' => '100.00',
                                'tarif' => '30.00',
                                'type_unite_id' => 2,
                                'quantite' => '3.25',
                                'tarif_min' => '40.00',
                                'tarif_pro_rata' => false,
                                'tarif_min_pour' => '1.00',
                                'tarif_min_pro_rata' => false,
                                'date' => '2019-01-01',
                                'heure' => '00:00:00',
                                'sapeur_id' => $this->sapeurThreeId,
                                'compte_id' => 4,
                                'intervention_id' => $this->interventionId,
                                'exercice_id' => null,
                                'ecriture_categorie_id' => 4,
                                'cours_sapeur_id' => null,
                                'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                                'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
                            ],
                        ]
                    ]
                ]
            );
    }

    /**
     * Test imputation avec tarif complexe (taux nuit appliqué)
     * 
     * Vérifie le calcul avec taux de nuit de 1.25 pour les heures de 20h à 08h.
     * Sapeur 1: 20h-12h15 = 16.25h dont 12h de nuit
     *
     * @return void
     * @throws Exception
     */
    public function testImputationImputationTarifComplex()
    {
        $param = array(
            "indemnite_intervention_type_id" => $this->indemniteTypeComplexId
        );
        $response = $this->json('POST', "/api/v2/imputation/intervention/$this->interventionId", $param);

        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    "data" => [
                        'statut' => InterventionBusiness::INTERVENTION_STATUT_IMPUTE,
                        'ecritures' => [
                            [
                                'complement' => Null,
                                'total' => '127.50',
                                'tarif' => '30.00',
                                'type_unite_id' => 2,
                                'quantite' => '4.25',
                                'tarif_min' => null,
                                'tarif_min_pour' => null,
                                'taux' => NULL,
                                'taux_description' => NULL,
                                'date' => '2019-01-01',
                                'heure' => '00:00:00',
                                'sapeur_id' => $this->sapeurOneId,
                                'compte_id' => 4,
                                'intervention_id' => $this->interventionId,
                                'exercice_id' => null,
                                'ecriture_categorie_id' => 4,
                                'cours_sapeur_id' => null,
                                'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                                'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
                            ],
                            [
                                'complement' => Null,
                                'total' => '450.00',
                                'tarif' => '30.00',
                                'type_unite_id' => 2,
                                'quantite' => '12.00',
                                'tarif_min' => null,
                                'tarif_min_pour' => null,
                                'taux' => '1.25',
                                'taux_description' => 'Nuit',
                                'date' => '2019-01-01',
                                'heure' => '00:00:00',
                                'sapeur_id' => $this->sapeurOneId,
                                'compte_id' => 4,
                                'intervention_id' => $this->interventionId,
                                'exercice_id' => null,
                                'ecriture_categorie_id' => 4,
                                'cours_sapeur_id' => null,
                                'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                                'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
                            ],
                            [
                                'complement' => Null,
                                'total' => '15.00',
                                'tarif' => '30.00',
                                'type_unite_id' => 2,
                                'quantite' => '0.50',
                                'tarif_min' => null,
                                'tarif_min_pour' => null,
                                'taux' => NULL,
                                'taux_description' => NULL,
                                'date' => '2019-01-01',
                                'heure' => '00:00:00',
                                'sapeur_id' => $this->sapeurTwoId,
                                'compte_id' => 4,
                                'intervention_id' => $this->interventionId,
                                'exercice_id' => null,
                                'ecriture_categorie_id' => 4,
                                'cours_sapeur_id' => null,
                                'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                                'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
                            ],
                            [
                                'complement' => Null,
                                'total' => '97.50',
                                'tarif' => '30.00',
                                'type_unite_id' => 2,
                                'quantite' => '3.25',
                                'tarif_min' => null,
                                'tarif_min_pour' => null,
                                'taux' => NULL,
                                'taux_description' => NULL,
                                'date' => '2019-01-01',
                                'heure' => '00:00:00',
                                'sapeur_id' => $this->sapeurThreeId,
                                'compte_id' => 4,
                                'intervention_id' => $this->interventionId,
                                'exercice_id' => null,
                                'ecriture_categorie_id' => 4,
                                'cours_sapeur_id' => null,
                                'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_SOLDE,
                                'module' => ImputationBusiness::ECRITURE_MODULE_INTERVENTION,
                            ],
                        ]
                    ]
                ]
            );
    }

    /**
     * Test imputation avec taux nuit et weekend
     * 
     * Teste 8 sapeurs avec différents patterns:
     * - Tarif standard unique
     * - Tarif nuit unique (1.25x)
     * - Tarif weekend unique (1.25x)
     * - Combinaisons de tarifs sur plusieurs jours
     * 
     * Weekend: 01-02 mars 2025 (samedi-dimanche)
     * Nuit: 20h-08h (configurable dans indemnite_type)
     *
     * @return void
     * @throws Exception
     */
    public function testImputationImputationTarifTauxNuitEtWeekend()
    {
        $param = ["indemnite_intervention_type_id" => $this->indemniteTypeComplexId];

        $intervention = Intervention::factory()->make();
        // Intervention du jeudi au mercredi incluant un weekend complet
        $intervention->exercice_comptable_id = 1;
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
            [
                // Tarif nuit + standard au quart d'heure
                'sapeur_id' => 8,
                'debut' => '2025-02-28 19:45',
                'fin' => '2025-02-28 20:45',
                'piquet' => 0
            ],
        ];

        $this->json('POST', "/api/v2/interventions/{$id}/sapeurs", ['sapeurs' => $sapeurs]);
        $this->json('POST', "/api/v2/interventions/{$id}/valider");

        $response = $this->json('POST', "/api/v2/imputation/intervention/$id", $param);
        $response
            ->assertStatus(200)
            ->assertJsonCount(16, 'data.ecritures')
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
                                "exercice_comptable_id" => 1,
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
                                "exercice_comptable_id" => 1,
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
                                "exercice_comptable_id" => 1,
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
                            [
                                "total" => "7.50",
                                "quantite" => "0.25",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => null,
                                "taux_description" => null,
                                "sapeur_id" => 8,
                                "type" => 1,
                                "module" => 2,
                            ],
                            [
                                "total" => "28.15",
                                "quantite" => "0.75",
                                "tarif" => "30.00",
                                "tarif_pro_rata" => true,
                                "tarif_min" => null,
                                "tarif_min_pour" => null,
                                "tarif_min_pro_rata" => false,
                                "taux" => "1.25",
                                "taux_description" => "Nuit",
                                "sapeur_id" => 8,
                                "type" => 1,
                                "module" => 2,
                            ],
                        ]
                    ]
                ]
            );
    }

    /**
     * Test imputation avec tarif min sur 2 jours avec phases et présences qui overlap
     * 
     * Scénario:
     * - Intervention du 10/01/2019 à 18h au 11/01/2019 à 10h
     * - Phase 1 (par défaut): début intervention
     * - Phase 2: 11/01/2019 à 02h00
     * - Sapeur 1: 10/01 20h - 11/01 08h (overlap la phase 2) + 2 périodes supplémentaires
     * - Sapeur 2: 10/01 18h - 10/01 22h + 11/01 03h - 11/01 06h (2 périodes distinctes)
     * - Sapeur 3: 11/01 01h - 11/01 09h (overlap la phase 2)
     *
     * @return void
     * @throws Exception
     */
    public function testImputationTarifMinAvecPhasesEtPresencesOverlap()
    {
        // Création d'une nouvelle intervention sur 2 jours
        $intervention = Intervention::factory()->make();
        $intervention->date_debut = '2019-01-10';
        $intervention->heure_debut = '18:00';
        $intervention->date_fin = '2019-01-11';
        $intervention->heure_fin = '10:00';

        $interventionId = $this->json('POST', '/api/v2/interventions', $intervention->toArray())->json('data.id');

        // Ajout d'une phase à 02h00 le 11/01
        $phases = [
            [
                'debut' => '2019-01-11 02:00',
                'phase_type_id' => 2, // Phase différente
                'intervention_id' => $interventionId,
            ]
        ];
        $this->json('POST', "/api/v2/interventions/{$interventionId}/phases", ['phases' => $phases]);

        // Ajout des 4 présences
        $sapeurs = [
            [
                // Sapeur 1: overlap la phase 2 (20h - 08h)
                // 20h-02h = 6h (phase 1), 02h-08h = 6h (phase 2)
                'sapeur_id' => $this->sapeurOneId,
                'debut' => '2019-01-10 20:00',
                'fin' => '2019-01-11 08:00',
                'piquet' => 0
            ],
            [
                // Sapeur 2, période 1: 18h-22h = 4h (phase 1)
                'sapeur_id' => $this->sapeurTwoId,
                'debut' => '2019-01-10 18:00',
                'fin' => '2019-01-10 22:00',
                'piquet' => 0
            ],
            [
                // Sapeur 2, période 2: 03h-06h = 3h (phase 2)
                'sapeur_id' => $this->sapeurTwoId,
                'debut' => '2019-01-11 03:00',
                'fin' => '2019-01-11 06:00',
                'piquet' => 0
            ],
            [
                // Sapeur 3: overlap la phase 2 (01h - 09h)
                // 01h-02h = 1h (phase 1), 02h-09h = 7h (phase 2)
                'sapeur_id' => $this->sapeurThreeId,
                'debut' => '2019-01-11 01:00',
                'fin' => '2019-01-11 09:00',
                'piquet' => 0
            ],
            [
                // Sapeur 1, période 1: 23h-04h = 5h (overlap les 2 phases!)
                // 23h-02h = 3h (phase 1), 02h-04h = 2h (phase 2)
                'sapeur_id' => $this->sapeurOneId,
                'debut' => '2019-01-10 23:00',
                'fin' => '2019-01-11 04:00',
                'piquet' => 0
            ],
            [
                // Sapeur 1, période 2: 07h-09h = 2h (phase 2)
                'sapeur_id' => $this->sapeurOneId,
                'debut' => '2019-01-11 07:00',
                'fin' => '2019-01-11 09:00',
                'piquet' => 0
            ],
        ];

        $this->json('POST', "/api/v2/interventions/{$interventionId}/sapeurs", ['sapeurs' => $sapeurs]);
        $this->json('POST', "/api/v2/interventions/{$interventionId}/valider");

        // Imputation avec tarif min
        $param = [
            "indemnite_intervention_type_id" => $this->indemniteTypeSimpleId
        ];
        $response = $this->json('POST', "/api/v2/imputation/intervention/$interventionId", $param);

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.ecritures')
            ->assertJson([
                "data" => [
                    'statut' => InterventionBusiness::INTERVENTION_STATUT_IMPUTE,
                    'ecritures' => [
                        [
                            // Sapeur 1: 19h total (3 périodes)
                            //   Période 1: 20h-08h = 12h
                            //   Période 2: 23h-04h = 5h (overlap: 3h phase 1 + 2h phase 2)
                            //   Période 3: 07h-09h = 2h
                            // Tarif: 40 CHF min (1h) + 18h × 30 CHF = 580 CHF
                            'total' => '580.00',
                            'tarif' => '30.00',
                            'quantite' => '19.00',
                            'tarif_min' => '40.00',
                            'tarif_min_pour' => '1.00',
                            'sapeur_id' => $this->sapeurOneId,
                        ],
                        [
                            // Sapeur 2: 7h total (2 périodes distinctes)
                            //   Période 1: 18h-22h = 4h (phase 1)
                            //   Période 2: 03h-06h = 3h (phase 2)
                            // Tarif: 40 CHF min (1h) + 6h × 30 CHF = 220 CHF
                            'total' => '220.00',
                            'tarif' => '30.00',
                            'quantite' => '7.00',
                            'tarif_min' => '40.00',
                            'tarif_min_pour' => '1.00',
                            'sapeur_id' => $this->sapeurTwoId,
                        ],
                        [
                            // Sapeur 3: 8h total (1 période overlap)
                            //   01h-09h = 8h (1h phase 1 + 7h phase 2)
                            // Tarif: 40 CHF min (1h) + 7h × 30 CHF = 250 CHF
                            'total' => '250.00',
                            'tarif' => '30.00',
                            'quantite' => '8.00',
                            'tarif_min' => '40.00',
                            'tarif_min_pour' => '1.00',
                            'sapeur_id' => $this->sapeurThreeId,
                        ],
                    ]
                ]
            ]);
    }

    /**
     * Test imputation avec tarif minimum calculé au pro-rata
     * 
     * Vérifie que lorsque tarif_min_pro_rata = true, le tarif minimum
     * est calculé proportionnellement à la durée effective.
     * 
     * Scénarios testés:
     * - 0.5h → 40 × (0.5/1) = 20 CHF (pro-rata)
     * - 3.25h → 40 × (1/1) + 30 × 2.25 = 107.50 CHF (min complet + reste pro-rata)
     * - 16.25h → 40 × (1/1) + 30 × 15.25 = 497.50 CHF (min complet + reste pro-rata)
     *
     * @return void
     * @throws Exception
     */
    public function testImputationTarifMinAvecProRata()
    {
        $param = [
            "indemnite_intervention_type_id" => $this->indemniteTypeProRataId
        ];
        $response = $this->json('POST', "/api/v2/imputation/intervention/$this->interventionId", $param);

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.ecritures')
            ->assertJson([
                "data" => [
                    'statut' => InterventionBusiness::INTERVENTION_STATUT_IMPUTE,
                    'ecritures' => [
                        [
                            // Sapeur 1: 16.25h total
                            // tarif_min_pro_rata = true, donc:
                            // - 1h au tarif min (40 CHF)
                            // - 15.25h au tarif normal pro-rata (15.25 × 30 = 457.50)
                            // Total = 40 + 457.50 = 497.50 CHF
                            'total' => '497.50',
                            'tarif' => '30.00',
                            'quantite' => '16.25',
                            'tarif_min' => '40.00',
                            'tarif_pro_rata' => true,
                            'tarif_min_pour' => '1.00',
                            'tarif_min_pro_rata' => true,
                            'sapeur_id' => $this->sapeurOneId,
                        ],
                        [
                            // Sapeur 2: 0.5h total (moins que tarif_min_pour)
                            // tarif_min_pro_rata = true, donc:
                            // - 0.5h au pro-rata du tarif min: 40 × (0.5/1) = 20 CHF
                            'total' => '20.00',
                            'tarif' => '30.00',
                            'quantite' => '0.50',
                            'tarif_min' => '40.00',
                            'tarif_pro_rata' => true,
                            'tarif_min_pour' => '1.00',
                            'tarif_min_pro_rata' => true,
                            'sapeur_id' => $this->sapeurTwoId,
                        ],
                        [
                            // Sapeur 3: 3.25h total
                            // tarif_min_pro_rata = true, donc:
                            // - 1h au tarif min (40 CHF)
                            // - 2.25h au tarif normal pro-rata (2.25 × 30 = 67.50)
                            // Total = 40 + 67.50 = 107.50 CHF
                            'total' => '107.50',
                            'tarif' => '30.00',
                            'quantite' => '3.25',
                            'tarif_min' => '40.00',
                            'tarif_pro_rata' => true,
                            'tarif_min_pour' => '1.00',
                            'tarif_min_pro_rata' => true,
                            'sapeur_id' => $this->sapeurThreeId,
                        ],
                    ]
                ]
            ]);
    }

    /**
     * Test de l'arrondi à l'heure inférieure avec tarif_pro_rata = false
     * 
     * Quand tarif_pro_rata = false, les heures excédentaires (au-delà du tarif_min_pour)
     * sont arrondies à l'heure inférieure avec floor().
     * 
     * Configuration: ID 1 avec tarif=30, tarif_min=40, tarif_min_pour=1h, tarif_pro_rata=false
     * 
     * Cas testés avec durées au quart d'heure:
     * 1. Sapeur 1: 4.75h (4h45) → 1h tarif min + floor(3.75) = 3h normal → 40 + 90 = 130 CHF
     * 2. Sapeur 2: 3.25h (3h15) → 1h tarif min + floor(2.25) = 2h normal → 40 + 60 = 100 CHF
     * 3. Sapeur 3: 2.5h (2h30) → 1h tarif min + floor(1.5) = 1h normal → 40 + 30 = 70 CHF
     * 
     * Comportement attendu: Les minutes au-delà des heures complètes ne sont pas payées
     * quand tarif_pro_rata = false.
     * 
     * @return void
     * @throws Exception
     */
    public function testImputationArrondiHeureInferieureAvecTarifProRataFalse()
    {
        // Créer une nouvelle intervention avec des durées précises au quart d'heure
        $intervention = Intervention::factory()->make();
        $interventionData = $intervention->toArray();
        $interventionData['date_debut'] = '2019-05-15';
        $interventionData['heure_debut'] = '10:00';
        $interventionData['date_fin'] = '2019-05-15';
        $interventionData['heure_fin'] = '18:00';

        $interventionId = $this->json('POST', '/api/v2/interventions', $interventionData)->json('data.id');

        // Présences avec durées au quart d'heure pour exposer le bug
        $sapeurs = [
            [
                'sapeur_id' => $this->sapeurOneId,
                'debut' => '2019-05-15 10:00',  // 4h45 (4.75h)
                'fin' => '2019-05-15 14:45',
                'piquet' => 0
            ],
            [
                'sapeur_id' => $this->sapeurTwoId,
                'debut' => '2019-05-15 11:30',  // 3h15 (3.25h)
                'fin' => '2019-05-15 14:45',
                'piquet' => 0
            ],
            [
                'sapeur_id' => $this->sapeurThreeId,
                'debut' => '2019-05-15 10:00',  // 2h30 (2.5h)
                'fin' => '2019-05-15 12:30',
                'piquet' => 0
            ],
        ];

        $this->json('POST', "/api/v2/interventions/{$interventionId}/sapeurs", ['sapeurs' => $sapeurs]);
        $this->json('POST', "/api/v2/interventions/{$interventionId}/valider");

        // Imputation avec tarif_pro_rata = false (tarif=30, tarif_min=40, tarif_min_pour=1)
        $param = [
            "indemnite_intervention_type_id" => $this->indemniteTypeSimpleId
        ];
        $response = $this->json('POST', "/api/v2/imputation/intervention/$interventionId", $param);

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.ecritures')
            ->assertJson([
                'data' => [
                    'ecritures' => [
                        [
                            // Sapeur 1: 4.75h total, tarif_min_pour = 1h, tarif_pro_rata = false
                            // Excédent = 4.75 - 1 = 3.75h → floor(3.75) = 3h (arrondi à l'heure inférieure)
                            // - 1h au tarif min: 40 CHF
                            // - 3h au tarif normal: 3 × 30 = 90 CHF
                            // Total = 130 CHF (les 45 minutes restantes ne sont pas payées)
                            'total' => '130.00',
                            'tarif' => '30.00',
                            'quantite' => '4.75',
                            'tarif_min' => '40.00',
                            'tarif_pro_rata' => false,
                            'tarif_min_pour' => '1.00',
                            'tarif_min_pro_rata' => false,
                            'sapeur_id' => $this->sapeurOneId,
                        ],
                        [
                            // Sapeur 2: 3.25h total, tarif_min_pour = 1h, tarif_pro_rata = false
                            // Excédent = 3.25 - 1 = 2.25h → floor(2.25) = 2h (arrondi à l'heure inférieure)
                            // - 1h au tarif min: 40 CHF
                            // - 2h au tarif normal: 2 × 30 = 60 CHF
                            // Total = 100 CHF (les 15 minutes restantes ne sont pas payées)
                            'total' => '100.00',
                            'tarif' => '30.00',
                            'quantite' => '3.25',
                            'tarif_min' => '40.00',
                            'tarif_pro_rata' => false,
                            'tarif_min_pour' => '1.00',
                            'tarif_min_pro_rata' => false,
                            'sapeur_id' => $this->sapeurTwoId,
                        ],
                        [
                            // Sapeur 3: 2.5h total, tarif_min_pour = 1h, tarif_pro_rata = false
                            // Excédent = 2.5 - 1 = 1.5h → floor(1.5) = 1h (arrondi à l'heure inférieure)
                            // - 1h au tarif min: 40 CHF
                            // - 1h au tarif normal: 1 × 30 = 30 CHF
                            // Total = 70 CHF (les 30 minutes restantes ne sont pas payées)
                            'total' => '70.00',
                            'tarif' => '30.00',
                            'quantite' => '2.50',
                            'tarif_min' => '40.00',
                            'tarif_pro_rata' => false,
                            'tarif_min_pour' => '1.00',
                            'tarif_min_pro_rata' => false,
                            'sapeur_id' => $this->sapeurThreeId,
                        ],
                    ]
                ]
            ]);
    }
}
