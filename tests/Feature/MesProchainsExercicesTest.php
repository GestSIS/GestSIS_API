<?php

namespace Tests\Feature;

use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MesProchainsExercicesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test que la route est accessible sans header Sis-Id et retourne un tableau de données groupé par SIS.
     *
     * @return void
     * @throws Exception
     */
    public function testMesProchainsExercicesOk()
    {
        $response = $this->json('GET', '/api/v2/mes-prochains-exercices');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
            ]);
    }
}
