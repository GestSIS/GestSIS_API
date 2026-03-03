<?php

namespace Tests\Feature;

use App\Domaine\API\ExerciceService;
use App\Infrastructure\Models\Exercice;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BaseDataExerciceTest extends TestCase
{
    use DatabaseTransactions;

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
                        'id',
                        'designation'
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
                        'id',
                        'designation'
                    ]
                ]
            ]);
    }
}
