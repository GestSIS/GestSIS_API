<?php

namespace Tests\Feature;

use App\Models\BatterieType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BatterieTypeTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexReturnsBatterieTypes(): void
    {
        $batterieType = BatterieType::create(['nom' => 'AAAA-index-test']);

        $response = $this->json('GET', '/api/v2/batterie-types');

        $response->assertStatus(200);
        $noms = array_column($response->json('data'), 'nom');
        $this->assertContains($batterieType->nom, $noms);
    }

    public function testStoreCreatesBatterieType(): void
    {
        $response = $this->json('POST', '/api/v2/batterie-types', [
            'nom' => 'CR123A',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['data' => ['id', 'nom']]);
        $this->assertDatabaseHas('batterie_types', ['nom' => 'CR123A']);
    }

    public function testUpdateModifiesBatterieType(): void
    {
        $batterieType = BatterieType::create(['nom' => 'Avant']);

        $response = $this->json('PUT', "/api/v2/batterie-types/{$batterieType->id}", [
            'nom' => 'Apres',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('batterie_types', ['id' => $batterieType->id, 'nom' => 'Apres']);
    }

    public function testDestroyDeletesBatterieType(): void
    {
        $batterieType = BatterieType::create(['nom' => 'A supprimer']);

        $response = $this->json('DELETE', "/api/v2/batterie-types/{$batterieType->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('batterie_types', ['id' => $batterieType->id]);
    }
}
