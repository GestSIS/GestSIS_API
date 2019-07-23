<?php

namespace Tests\Unit;

use App\Domaine\API\ExerciceService;
use App\Infrastructure\Models\Exercice;
use Exception;
use Tests\TestCase;

class ExerciceComptableTest extends TestCase
{

    /**
     * Test index groupe
     *
     * @return void
     * @throws Exception
     */
    public function testExerciceComptableIndexOK()
    {
        $response = $this->json('GET', "/api/v2/exercice-comptables/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'designation', 'annee', 'debut', 'fin'
                    ]
                ]
            ]);
    }

}
