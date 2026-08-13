<?php

namespace Tests\Feature;

use App\Models\Permis;
use App\Models\Sapeur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurPermisTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test index returns list of permis
     */
    public function testIndexPermisReturnsListOfPermis()
    {
        $sapeur = Sapeur::factory()->create();

        // Create 3 permis using factory
        $permis1 = Permis::factory()->create(['sapeur_id' => $sapeur->id, 'permis_type_id' => 1]);
        $permis2 = Permis::factory()->create(['sapeur_id' => $sapeur->id, 'permis_type_id' => 2]);
        $permis3 = Permis::factory()->create(['sapeur_id' => $sapeur->id, 'permis_type_id' => 3]);

        $response = $this->json('GET', "/api/v2/sapeurs/{$sapeur->id}/permis");

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonFragment(['id' => $permis1->id])
            ->assertJsonFragment(['id' => $permis2->id])
            ->assertJsonFragment(['id' => $permis3->id]);
    }

    /**
     * Test index returns error when sapeur not found
     */
    public function testIndexPermisReturnsErrorWhenSapeurNotFound()
    {
        $response = $this->json('GET', "/api/v2/sapeurs/99999/permis");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    /**
     * Test add permis successfully
     */
    public function testAddPermisSuccessfully()
    {
        $sapeur = Sapeur::factory()->create();
        $permis_type = 9;

        $response = $this->json(
            'POST',
            "/api/v2/sapeurs/{$sapeur->id}/permis",
            ['permis_type_id' => $permis_type, 'date' => '1958-01-01']
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'permis_type_id',
                    'sapeur_id',
                    'date'
                ]
            ]);

        $permis = $response->getData()->data;
        $this->assertEquals($permis_type, $permis->permis_type_id);
        $this->assertEquals($sapeur->id, $permis->sapeur_id);
    }

    /**
     * Test add permis returns error when sapeur not found
     */
    public function testAddPermisReturnsErrorWhenSapeurNotFound()
    {
        $response = $this->json(
            'POST',
            "/api/v2/sapeurs/99999/permis",
            ['permis_type_id' => 1, 'date' => '1958-01-01']
        );

        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    /**
     * Test add permis successfully for a recrue (pas encore un sapeur validé)
     */
    public function testAddPermisSuccessfullyForRecrue()
    {
        $recrue = Sapeur::factory()->create(['type' => 2]); // TYPE_RECRUE

        $response = $this->json(
            'POST',
            "/api/v2/sapeurs/{$recrue->id}/permis",
            ['permis_type_id' => 1, 'date' => '2020-01-01']
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'permis_type_id', 'sapeur_id', 'date']]);
    }

    /**
     * Test add permis returns error for a civil
     */
    public function testAddPermisReturnsErrorForCivil()
    {
        $civil = Sapeur::factory()->create(['type' => 1]); // TYPE_CIVIL

        $response = $this->json(
            'POST',
            "/api/v2/sapeurs/{$civil->id}/permis",
            ['permis_type_id' => 1, 'date' => '2020-01-01']
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['error']);
    }

    /**
     * Test add permis returns error when duplicated
     */
    public function testAddPermisReturnsErrorWhenDuplicated()
    {
        $sapeur = Sapeur::factory()->create();
        $permis_type = 4;
        $date = '1958-01-01';

        // Create first permis
        $this->json(
            'POST',
            "/api/v2/sapeurs/{$sapeur->id}/permis",
            ['permis_type_id' => $permis_type, 'date' => $date]
        );

        // Try to create duplicate
        $response = $this->json(
            'POST',
            "/api/v2/sapeurs/{$sapeur->id}/permis",
            ['permis_type_id' => $permis_type, 'date' => $date]
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['error']);
    }

    /**
     * Test edit permis successfully
     */
    public function testEditPermisSuccessfully()
    {
        $sapeur = Sapeur::factory()->create();
        $permis_type = 2;

        // Create permis
        $createResponse = $this->json(
            'POST',
            "/api/v2/sapeurs/{$sapeur->id}/permis",
            ['permis_type_id' => $permis_type, 'date' => '1958-01-01']
        );
        $permis = $createResponse->getData()->data;

        $newDate = '1999-11-21';

        // Update permis
        $response = $this->json(
            'PUT',
            "/api/v2/sapeurs/{$sapeur->id}/permis/{$permis->id}",
            ['id' => $permis->id, 'date' => $newDate]
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'permis_type_id',
                    'sapeur_id',
                    'date'
                ]
            ]);

        $updatedPermis = $response->getData()->data;
        $this->assertEquals($newDate, date('Y-m-d', strtotime($updatedPermis->date)));
    }

    /**
     * Test edit permis returns error when not found
     */
    public function testEditPermisReturnsErrorWhenNotFound()
    {
        $sapeur = Sapeur::factory()->create();

        $response = $this->json(
            'PUT',
            "/api/v2/sapeurs/{$sapeur->id}/permis/99999",
            ['id' => 99999, 'date' => '1999-11-21']
        );

        $response->assertStatus(404)
            ->assertJson(['error' => 'Permis non trouvé']);
    }

    /**
     * Test edit permis returns error when sapeur not found
     */
    public function testEditPermisReturnsErrorWhenSapeurNotFound()
    {
        $response = $this->json(
            'PUT',
            "/api/v2/sapeurs/99999/permis/1",
            ['id' => 1, 'date' => '1999-11-21']
        );

        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    /**
     * Test remove permis successfully
     */
    public function testRemovePermisSuccessfully()
    {
        $sapeur = Sapeur::factory()->create();
        $permis_type = 7;

        // Create permis
        $createResponse = $this->json(
            'POST',
            "/api/v2/sapeurs/{$sapeur->id}/permis",
            ['permis_type_id' => $permis_type, 'date' => '1958-01-01']
        );
        $permis = $createResponse->getData()->data;

        // Delete permis
        $response = $this->json('DELETE', "/api/v2/sapeurs/{$sapeur->id}/permis/{$permis->id}");

        $response
            ->assertStatus(200)
            ->assertJson(['data' => 'success']);

        // Verify it's deleted
        $indexResponse = $this->json('GET', "/api/v2/sapeurs/{$sapeur->id}/permis");
        $permisList = $indexResponse->getData()->data;
        $found = array_filter($permisList, fn($p) => $p->id == $permis->id);
        $this->assertEmpty($found);
    }

    /**
     * Test remove permis returns error when not found
     */
    public function testRemovePermisReturnsErrorWhenNotFound()
    {
        $sapeur = Sapeur::factory()->create();

        $response = $this->json('DELETE', "/api/v2/sapeurs/{$sapeur->id}/permis/99999");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Permis non trouvé']);
    }

    /**
     * Test remove permis returns error when sapeur not found
     */
    public function testRemovePermisReturnsErrorWhenSapeurNotFound()
    {
        $response = $this->json('DELETE', "/api/v2/sapeurs/99999/permis/1");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }
}
