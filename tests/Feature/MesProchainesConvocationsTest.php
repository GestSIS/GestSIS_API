<?php

namespace Tests\Feature;

use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MesProchainesConvocationsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test que la route est accessible sans header Sis-Key et retourne un tableau de données groupé par SIS.
     *
     * @return void
     * @throws Exception
     */
    public function testMesProchainesConvocationsOk()
    {
        $response = $this->json('GET', '/api/v2/mes-prochaines-convocations');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
            ]);
    }
}
