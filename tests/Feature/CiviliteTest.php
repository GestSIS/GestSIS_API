<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CiviliteTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexCivilitesReturnsListOfCivilites(): void
    {
        $response = $this->json('GET', '/api/v2/civilites/', [], [
            'Sis-Key' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'designation',
                ],
            ],
        ]);
    }
}
