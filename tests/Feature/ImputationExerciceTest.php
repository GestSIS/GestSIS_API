<?php

namespace Test\Feature;

use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\Sapeur;
use Exception;
use Tests\TestCase;
use App\Domaine\API\ExerciceService;
use App\Domaine\API\SapeurService;
use App\Domaine\API\ComptabiliteService;

class ImputationExerciceTest extends TestCase
{

    protected $comptabiliteService;
    protected $sapeurOneId;
    protected $sapeurTwoId;
    protected $sapeurThreeId;
    protected $exerciceId;

    protected function setUp(): void
    {
        parent::setUp();

        $exerciceService = $this->app->make(ExerciceService::class);
        $sapeurService = $this->app->make(SapeurService::class);
        $this->comptabiliteService = $this->app->make(ComptabiliteService::class);

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurOneId = $sapeurService->createSapeur($data)->id;;

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurTwoId = $sapeurService->createSapeur($data)->id;;

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurThreeId = $sapeurService->createSapeur($data)->id;

        $this->exerciceId = $exerciceService->createExercice(Exercice::factory()->make()->toArray())->id;

        $exerciceService->addSapeurs($this->exerciceId, array(
            array(
                'sapeur_id' => $this->sapeurOneId,
                'convoque' => 1,
                'present' => 1,
                'amende' => 0,
                'remplace' => 0,
                'excuse_type_id' => null
            ),
            array(
                'sapeur_id' => $this->sapeurTwoId,
                'convoque' => 1,
                'present' => 0,
                'amende' => 1,
                'remplace' => 0,
                'excuse_type_id' => 4
            ),
            array(
                'sapeur_id' => $this->sapeurThreeId,
                'convoque' => 1,
                'present' => 1,
                'amende' => 0,
                'remplace' => 0,
                'excuse_type_id' => null
            )
        ));
        $exerciceService->validateExercice($this->exerciceId);
    }

    /**
     * Test add exercice
     *
     * @return void
     * @throws Exception
     */
    public function testImputationExerciceParPiece()
    {
        $param = array(
            "indemnite_exercice_type_id" => 1
        );

        $ecritures = $this->comptabiliteService->imputationExercice($this->exerciceId, $param);
        //$response = $this->json('POST', '/api/v2/imputation/exercice/' . $this->exerciceId, $param);

        $this->assertTrue(count($ecritures) === 2);
    }

    /**
     * Test add exercice
     *
     * @return void
     * @throws Exception
     */
    public function testImputationExerciceParHeureEtFonction()
    {
        $param = array(
            "indemnite_exercice_type_id" => 10
        );

        $ecritures = $this->comptabiliteService->imputationExercice($this->exerciceId, $param);
        //$response = $this->json('POST', '/api/v2/imputation/exercice/' . $this->exerciceId, $param);

        $this->assertTrue(count($ecritures) === 2);
    }

    /**
     * Test add exercice
     *
     * @return void
     * @throws Exception
     */
    public function testImputationExerciceParHeureEtSoldeMin()
    {
        $param = array(
            "indemnite_exercice_type_id" => 2
        );

        $ecritures = $this->comptabiliteService->imputationExercice($this->exerciceId, $param);
        //$response = $this->json('POST', '/api/v2/imputation/exercice/' . $this->exerciceId, $param);

        $this->assertTrue(count($ecritures) === 2);
    }
}
