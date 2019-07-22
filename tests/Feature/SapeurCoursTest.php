<?php

namespace Tests\Feature;

use App\Domaine\API\SapeurService;
use Tests\TestCase;

class SapeurTest extends TestCase
{

    protected $sapeurService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sapeurService = $this->app->make(SapeurService::class);
    }

    public function testIndexSapeurCours()
    {
        $response = $this->json('GET', '/api/v2/sapeurs/1/groupes');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'groupe_id', 'sapeur_id']
                ]
            ]);
    }

}
