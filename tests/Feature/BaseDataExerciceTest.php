<?php

namespace Test\Feature;

use App\Domaine\API\ExerciceService;
use App\Infrastructure\Models\Exercice;
use Exception;
use Tests\TestCase;

class BaseDataExerciceTest extends TestCase
{

    /**
     * Test index exercices-categorie
     *
     * @return void
     * @throws Exception
     */
    public function testExerciceCategorieIndexOk()
    {
        $response = $this->json('GET', "/api/v2/exercice-categories/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'designation'
                    ]
                ]
            ]);
    }

    /**
     * Test index excuses-types
     *
     * @return void
     * @throws Exception
     */
    public function testExcuseTypesIndexOk()
    {
        $response = $this->json('GET', "/api/v2/excuses-types/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'designation'
                    ]
                ]
            ]);
    }

}
