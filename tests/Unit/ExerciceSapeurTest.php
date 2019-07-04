<?php

namespace Tests\Unit;

use App\Models\Exercice;
use Exception;
use Tests\TestCase;

class ExerciceSapeurTest extends TestCase
{

    protected $exerciceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exerciceService = $this->app->make('App\Services\ExerciceService');
    }

    /**
     * Test add grade
     *
     * @return void
     * @throws Exception
     */
    public function testAddExerciceSapeurs()
    {
        $exercice = factory(Exercice::class)->create();

        $sapeurs = array(
            'sapeurs' => array(
                array(
                    'sapeur_id' => 1,
                    'convoque' => 1,
                    'present' => 1,
                    'amende' => 0,
                    'remplace' => 0,
                    'excuse_type_id' => null
                ),
                array(
                    'sapeur_id' => 2,
                    'convoque' => 1,
                    'present' => 0,
                    'amende' => 1,
                    'remplace' => 0,
                    'excuse_type_id' => 4
                ),
                array(
                    'sapeur_id' => 3,
                    'convoque' => 1,
                    'present' => 0,
                    'amende' => 0,
                    'remplace' => 0,
                    'excuse_type_id' => null
                ),
            )
        );

        $response = $this->json('POST', '/api/v2/exercices/' . $exercice->id . '/sapeurs', $sapeurs);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test edit grade
     *
     * @return void
     * @throws Exception
     */
    public function testEditExerciceSapeurs()
    {
        $exercice = factory(Exercice::class)->make();
        $exercice = $this->exerciceService->createExercice($exercice->toArray());

        $sapeurs = [
            array(
                'sapeur_id' => 1,
                'convoque' => 1,
                'present' => 1,
                'amende' => 0,
                'remplace' => 0,
                'excuse_type_id' => null
            ),
            array(
                'sapeur_id' => 2,
                'convoque' => 1,
                'present' => 0,
                'amende' => 1,
                'remplace' => 0,
                'excuse_type_id' => 4
            ),
            array(
                'sapeur_id' => 3,
                'convoque' => 1,
                'present' => 0,
                'amende' => 0,
                'remplace' => 0,
                'excuse_type_id' => null
            )
        ];

        $sapeurs = $this->exerciceService->addSapeurs($exercice->id, $sapeurs);

        $sapeurs[1]->present = 0;
        $sapeurs[1]->excuse_type_id = 1;

        $response = $this->json('PUT', '/api/v2/exercices/' . $exercice->id . '/sapeurs/', array("sapeurs" => $sapeurs));

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test remove grade
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveExerciceSapeurs()
    {
        $exercice = factory(Exercice::class)->make();
        $exercice = $this->exerciceService->createExercice($exercice->toArray());

        $sapeurs = [
            array(
                'sapeur_id' => 1,
                'convoque' => 1,
                'present' => 1,
                'amende' => 0,
                'remplace' => 0,
                'excuse_type_id' => null
            ),
            array(
                'sapeur_id' => 2,
                'convoque' => 1,
                'present' => 0,
                'amende' => 1,
                'remplace' => 0,
                'excuse_type_id' => 4
            ),
            array(
                'sapeur_id' => 3,
                'convoque' => 1,
                'present' => 0,
                'amende' => 0,
                'remplace' => 0,
                'excuse_type_id' => null
            )
        ];

        $sapeurs = $this->exerciceService->addSapeurs($exercice->id, $sapeurs);

        $ids = array_map(function ($sap) {
            return $sap->id;
        }, $sapeurs);

        $response = $this->json('DELETE', '/api/v2/exercices/' . $exercice->id . '/sapeurs/', array("sapeurs" => $ids));
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }
}
