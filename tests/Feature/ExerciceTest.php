<?php

namespace Tests\Feature;

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
     * Test add permis
     *
     * @return void
     * @throws Exception
     */
    public function testExerciceIndexLimitedOk()
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
    public function testExerciceShowOk()
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
    public function testAddExerciceOk()
    {
        $exercice = Exercice::factory()->make();

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
        $exercice = Exercice::factory()->create();
        $exerciceEdited = Exercice::factory()->make();

        $response = $this->json(
            'PUT',
            '/api/v2/exercices/' . $exercice->id,
            $exerciceEdited->toArray()
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
        $exercice = Exercice::factory()->create();

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
    public function testValidateExerciceOk()
    {
        $exercice = Exercice::factory()->create();

        $sapeurs = [
            [
                'sapeur_id' => 1,
                'convoque' => 1,
                'present' => 1,
                'absent' => 0,
                'amende' => 0,
                'remplace' => 0,
                'excuse_type_id' => null
            ],
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
        $exercice = Exercice::factory()->create();

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
