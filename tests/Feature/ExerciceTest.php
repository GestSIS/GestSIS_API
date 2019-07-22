<?php

namespace Tests\Unit;

use App\Domaine\API\ExerciceService;
use App\Infrastructure\Models\Exercice;
use Exception;
use Tests\TestCase;

class ExerciceTest extends TestCase
{

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(ExerciceService::class);
    }

    /**
     * Test index exercices
     *
     * @return void
     * @throws Exception
     */
    public function testExerciceIndexAllOK()
    {
        $response = $this->json('GET', "/api/v2/exercices/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'designation', 'localite_id', 'date', 'lieu', 'heure', 'duree'
                    ]
                ]
            ]);
    }

    /**
     * Test add permis
     *
     * @return void
     * @throws Exception
     */
    public function testExerciceIndexLimitedOK()
    {
        $response = $this->json('GET', "/api/v2/exercices?exercice_comptable_id=3");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'designation', 'localite_id', 'date', 'lieu', 'heure', 'duree'
                    ]
                ]
            ]);
    }

    /**
     * Test add permis
     *
     * @return void
     * @throws Exception
     */
    public function testExerciceShowOK()
    {
        $response = $this->json('GET', "/api/v2/exercices/1");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'designation', 'localite_id', 'date', 'lieu', 'heure', 'duree'
                ]
            ]);
    }

    /**
     * Test show exercice
     *
     * @return void
     * @throws Exception
     */
    public function testAddExerciceOK()
    {
        $exercice = factory(Exercice::class)->make();

        $response = $this->json('POST', '/api/v2/exercices', $exercice->toArray());

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test edit exercice
     *
     * @return void
     * @throws Exception
     */
    public function testEditExercice()
    {
        $exercice = factory(Exercice::class)->create();
        $exerciceEdited = factory(Exercice::class)->make();

        $response = $this->json(
            'PUT',
            '/api/v2/exercices/' . $exercice->id, $exerciceEdited->toArray()
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test validate exercice
     *
     * @return void
     * @throws Exception
     */
    public function testValidateExerciceInvalid()
    {
        $exercice = factory(Exercice::class)->create();

        $response = $this->json('POST', "/api/v2/exercices/$exercice->id/valider");

        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => true
            ]);
    }

    /**
     * Test validate exercice
     *
     * @return void
     * @throws Exception
     */
    public function testValidateExerciceOK()
    {
        $exercice = factory(Exercice::class)->create();

        $sapeurs = [
            array(
                'sapeur_id' => 1,
                'convoque' => 1,
                'present' => 1,
                'amende' => 0,
                'remplace' => 0,
                'excuse_type_id' => null
            ),
        ];
        $this->service->addSapeurs($exercice->id, $sapeurs);

        $response = $this->json('POST', "/api/v2/exercices/$exercice->id/valider");

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test remove exercice
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveExercice()
    {
        $exercice = factory(Exercice::class)->create();

        $response = $this->json(
            'DELETE',
            '/api/v2/exercices/' . $exercice->id
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => 'success'
            ]);
    }
}
