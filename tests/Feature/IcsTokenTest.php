<?php

namespace Tests\Feature;

use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IcsTokenTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test que la route est accessible sans header Sis-Key et retourne un tableau de liens groupé par SIS.
     *
     * @return void
     * @throws Exception
     */
    public function testIcsTokensIndexOk()
    {
        $response = $this->json('GET', '/api/v2/ics-tokens');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
            ]);
    }

    /**
     * Test que la régénération est refusée pour un SIS auquel le sapeur connecté n'appartient pas.
     *
     * @return void
     * @throws Exception
     */
    public function testRegenerateRefusedForUnknownSis()
    {
        $response = $this->json('POST', '/api/v2/ics-tokens/unknown-sis/regenerate');

        $response
            ->assertStatus(403)
            ->assertJson([
                'error' => 'Sis inconnu',
            ]);
    }
}
