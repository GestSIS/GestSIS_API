<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Sapeur;
use App\Infrastructure\Models\SapeurTelephone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SapeurTelephoneTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test index returns list of telephones
     */
    public function testIndexTelephonesReturnsListOfTelephones()
    {
        $sapeur = Sapeur::factory()->create();

        // Create 3 telephones using factory
        $tel1 = SapeurTelephone::factory()->create(['sapeur_id' => $sapeur->id, 'telephone_type_id' => 1]);
        $tel2 = SapeurTelephone::factory()->create(['sapeur_id' => $sapeur->id, 'telephone_type_id' => 2]);
        $tel3 = SapeurTelephone::factory()->create(['sapeur_id' => $sapeur->id, 'telephone_type_id' => 3]);

        $response = $this->json('GET', "/api/v2/sapeurs/{$sapeur->id}/telephones");

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonFragment(['id' => $tel1->id])
            ->assertJsonFragment(['id' => $tel2->id])
            ->assertJsonFragment(['id' => $tel3->id]);
    }

    /**
     * Test index returns error when sapeur not found
     */
    public function testIndexTelephonesReturnsErrorWhenSapeurNotFound()
    {
        $response = $this->json('GET', "/api/v2/sapeurs/99999/telephones");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    /**
     * Test add telephone successfully
     */
    public function testAddTelephoneSuccessfully()
    {
        $sapeur = Sapeur::factory()->create();
        $data = [
            'numero' => '032 546 54 12',
            'telephone_type_id' => 1,
            'rta' => false,
            'priorite' => 1
        ];

        $response = $this->json('POST', "/api/v2/sapeurs/{$sapeur->id}/telephones", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'telephone_type_id',
                    'sapeur_id',
                    'rta',
                    'priorite',
                    'numero'
                ]
            ]);

        $telephone = $response->getData()->data;
        $this->assertEquals($data['numero'], $telephone->numero);
        $this->assertEquals($data['telephone_type_id'], $telephone->telephone_type_id);
        $this->assertEquals($sapeur->id, $telephone->sapeur_id);
    }

    /**
     * Test add telephone returns error when sapeur not found
     */
    public function testAddTelephoneReturnsErrorWhenSapeurNotFound()
    {
        $data = [
            'numero' => '032 546 54 12',
            'telephone_type_id' => 1,
            'rta' => false,
            'priorite' => 1
        ];

        $response = $this->json('POST', "/api/v2/sapeurs/99999/telephones", $data);

        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    /**
     * Test edit telephone successfully
     */
    public function testEditTelephoneSuccessfully()
    {
        $sapeur = Sapeur::factory()->create();

        // Create telephone
        $telephone = SapeurTelephone::factory()->create([
            'sapeur_id' => $sapeur->id,
            'numero' => '032 546 54 15',
            'telephone_type_id' => 1,
            'rta' => false,
            'priorite' => 1
        ]);

        $newData = [
            'id' => $telephone->id,
            'numero' => '032 546 12 18',
            'telephone_type_id' => 2,
            'rta' => true,
            'priorite' => 3
        ];

        $response = $this->json(
            'PUT',
            "/api/v2/sapeurs/{$sapeur->id}/telephones/{$telephone->id}",
            $newData
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'telephone_type_id',
                    'sapeur_id',
                    'rta',
                    'priorite',
                    'numero'
                ]
            ]);

        $updatedTelephone = $response->getData()->data;
        $this->assertEquals($newData['numero'], $updatedTelephone->numero);
        $this->assertEquals($newData['telephone_type_id'], $updatedTelephone->telephone_type_id);
        $this->assertEquals($newData['rta'], $updatedTelephone->rta);
        $this->assertEquals($newData['priorite'], $updatedTelephone->priorite);
    }

    /**
     * Test edit telephone returns error when not found
     */
    public function testEditTelephoneReturnsErrorWhenNotFound()
    {
        $sapeur = Sapeur::factory()->create();

        $data = [
            'id' => 99999,
            'numero' => '032 546 12 18',
            'telephone_type_id' => 2,
            'rta' => true,
            'priorite' => 3
        ];

        $response = $this->json(
            'PUT',
            "/api/v2/sapeurs/{$sapeur->id}/telephones/99999",
            $data
        );

        $response->assertStatus(404)
            ->assertJson(['error' => 'Téléphone non trouvé']);
    }

    /**
     * Test edit telephone returns error when sapeur not found
     */
    public function testEditTelephoneReturnsErrorWhenSapeurNotFound()
    {
        $data = [
            'id' => 1,
            'numero' => '032 546 12 18',
            'telephone_type_id' => 2,
            'rta' => true,
            'priorite' => 3
        ];

        $response = $this->json(
            'PUT',
            "/api/v2/sapeurs/99999/telephones/1",
            $data
        );

        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }

    /**
     * Test remove telephone successfully
     */
    public function testRemoveTelephoneSuccessfully()
    {
        $sapeur = Sapeur::factory()->create();

        // Create telephone
        $telephone = SapeurTelephone::factory()->create([
            'sapeur_id' => $sapeur->id,
            'numero' => '032 546 54 79'
        ]);

        $response = $this->json('DELETE', "/api/v2/sapeurs/{$sapeur->id}/telephones/{$telephone->id}");

        $response
            ->assertStatus(200)
            ->assertJson(['data' => 'success']);

        // Verify it's deleted
        $this->assertDatabaseMissing('sapeur_telephone', [
            'id' => $telephone->id,
            'sapeur_id' => $sapeur->id
        ]);
    }

    /**
     * Test remove telephone returns error when not found
     */
    public function testRemoveTelephoneReturnsErrorWhenNotFound()
    {
        $sapeur = Sapeur::factory()->create();

        $response = $this->json('DELETE', "/api/v2/sapeurs/{$sapeur->id}/telephones/99999");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Téléphone non trouvé']);
    }

    /**
     * Test remove telephone returns error when sapeur not found
     */
    public function testRemoveTelephoneReturnsErrorWhenSapeurNotFound()
    {
        $response = $this->json('DELETE', "/api/v2/sapeurs/99999/telephones/1");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Sapeur non trouvé']);
    }
}
