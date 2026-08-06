<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LocaliteTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexLocalitesReturnsListOfLocalites(): void
    {
        $response = $this->json('GET', '/api/v2/localites/', [], [
            'Sis-Key' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'designation',
                    'npa',
                ],
            ],
        ]);
    }
}
