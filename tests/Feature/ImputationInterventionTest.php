<?php

namespace Test\Feature;

use App\Infrastructure\Models\Intervention;
use App\Infrastructure\Models\Sapeur;
use Exception;
use Tests\TestCase;
use App\Domaine\API\InterventionService;
use App\Domaine\API\SapeurService;
use App\Domaine\API\ImputationService;
use Carbon\Carbon;

class ImputationInterventionTest extends TestCase
{

    protected $comptabiliteService;
    protected $sapeurOneId;
    protected $sapeurTwoId;
    protected $sapeurThreeId;
    protected $interventionId;

    protected function setUp(): void
    {
        parent::setUp();

        $interventionService = $this->app->make(InterventionService::class);
        $sapeurService = $this->app->make(SapeurService::class);
        $this->comptabiliteService = $this->app->make(ImputationService::class);

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurOneId = $sapeurService->createSapeur($data)->id;

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurTwoId = $sapeurService->createSapeur($data)->id;

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurThreeId = $sapeurService->createSapeur($data)->id;

        $intervention = Intervention::factory()->make();
        $intervention->date_debut = Carbon::createMidnightDate(2019, 1, 1);
        $intervention->date_fin = Carbon::createMidnightDate(2019, 1, 3);

        $this->interventionId = $interventionService->createIntervention($intervention->toArray())->id;

        $sapeurs = array(
            array(
                'sapeur_id' => 1,
                'debut' => '2019-01-01 20:00',
                'fin' => '2019-01-02 12:15',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => 2,
                'debut' => '2019-01-01 12:00',
                'fin' => '2019-01-01 12:30',
                'piquet' => 0
            ),
            array(
                'sapeur_id' => 3,
                'debut' => '2019-01-01 12:15',
                'fin' => '2019-01-01 15:30',
                'piquet' => 0
            ),
        );

        $interventionService->addPresences($this->interventionId, $sapeurs);
        $interventionService->validerInterventionById($this->interventionId);
    }

    /**
     * Test add exercice
     *
     * @return void
     * @throws Exception
     */
    public function testImputationImputationTarifSimple()
    {
        $param = array(
            "indemnite_intervention_type_id" => 1
        );
        $response = $this->json('POST', "/api/v2/imputation/intervention/$this->interventionId", $param);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);

        $ecritures = $this->comptabiliteService->getEcrituresForInterventionById($this->interventionId);

        //$this->assertTrue(count($ecritures) === 3);
    }

    /**
     * Test add exercice
     *
     * @return void
     * @throws Exception
     */
    public function testImputationImputationTarifComplex()
    {
        $param = array(
            "indemnite_intervention_type_id" => 2
        );
        $response = $this->json('POST', "/api/v2/imputation/intervention/$this->interventionId", $param);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);

        $ecritures = $this->comptabiliteService->getEcrituresForInterventionById($this->interventionId);
        //dd($ecritures);
        //$this->assertTrue(count($ecritures) === 3);
    }
    //    /**
    //     * Test add exercice
    //     *
    //     * @return void
    //     * @throws Exception
    //     */
    //    public function testImputationLonguePériode()
    //    {
    //        $param = array(
    //            "indemnite_exercice_type_id" => 10
    //        );
    //
    //        $ecritures = $this->comptabiliteService->imputationExercice($this->interventionId, $param);
    //        //$response = $this->json('POST', '/api/v2/imputation/exercice/' . $this->interventionId, $param);
    //
    //        $this->assertTrue(count($ecritures) === 2);
    //    }
}
