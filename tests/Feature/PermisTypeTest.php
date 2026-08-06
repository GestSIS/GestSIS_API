<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PermisTypeTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexPermisTypesReturnsListOfPermisTypes(): void
    {
        $response = $this->json('GET', '/api/v2/permis/', [], [
            'Sis-Key' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'type',
                ],
            ],
        ]);
    }
}
