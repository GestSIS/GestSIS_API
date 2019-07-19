<?php

namespace Tests\Unit;

use App\Infrastructure\Models\Exercice;
use Exception;
use Tests\TestCase;

class ExerciceTest extends TestCase
{
    /**
     * Test add grade
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
     * Test edit grade
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
     * Test remove grade
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
