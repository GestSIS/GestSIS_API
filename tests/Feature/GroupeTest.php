<?php

namespace Test\Feature;

use App\Domaine\API\ExerciceService;
use App\Infrastructure\Models\Exercice;
use Exception;
use Tests\TestCase;

class GroupeTest extends TestCase
{

    /**
     * Test index groupe
     *
     * @return void
     * @throws Exception
     */
    public function testGroupeIndexOk()
    {
        $response = $this->json('GET', "/api/v2/groupes/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'designation', 'no', 'actif'
                    ]
                ]
            ]);
    }

}
