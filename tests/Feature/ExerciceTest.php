<?php

namespace Tests\Feature;

use App\Models\Exercice;
use App\Models\Sapeur;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExerciceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test index exercices with filter
     */
    public function testIndexExercicesWithFilter()
    {
        $exercice1 = Exercice::factory()->create(['exercice_comptable_id' => 5]);
        $exercice2 = Exercice::factory()->create(['exercice_comptable_id' => 5]);
        Exercice::factory()->create(['exercice_comptable_id' => 6]); // Should not appear

        $response = $this->json('GET', "/api/v2/exercices?exercice_comptable_id=5");

        $response
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'designation',
                        'localite_id',
                        'date',
                        'lieu',
                        'heure',
                        'duree',
                        'exercice_comptable_id'
                    ]
                ]
            ]);
    }

    /**
     * Test show exercice successfully
     */
    public function testShowExerciceSuccessfully()
    {
        $exercice = Exercice::factory()->create([
            'designation' => 'Exercice Test',
            'lieu' => 'Caserne'
        ]);

        $response = $this->json('GET', "/api/v2/exercices/{$exercice->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $exercice->id,
                    'designation' => 'Exercice Test',
                    'lieu' => 'Caserne'
                ]
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'designation',
                    'localite_id',
                    'date',
                    'lieu',
                    'heure',
                    'duree'
                ]
            ]);
    }

    /**
     * Test show exercice returns 404 when not found
     */
    public function testShowExerciceReturnsNullWhenNotFound()
    {
        $response = $this->json('GET', "/api/v2/exercices/99999");

        $response->assertStatus(404);
    }

    /**
     * Test create exercice successfully
     */
    public function testCreateExerciceSuccessfully()
    {
        $exerciceData = Exercice::factory()->make([
            'designation' => 'Nouvel Exercice',
            'lieu' => 'Centre de formation'
        ])->toArray();

        $response = $this->json('POST', '/api/v2/exercices', $exerciceData);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);

        $this->assertDatabaseHas('exercices', [
            'designation' => 'Nouvel Exercice',
            'lieu' => 'Centre de formation'
        ]);
    }

    /**
     * Test update exercice successfully
     */
    public function testUpdateExerciceSuccessfully()
    {
        $exercice = Exercice::factory()->create(['designation' => 'Ancien nom']);

        $updatedData = Exercice::factory()->make([
            'designation' => 'Nouveau nom',
            'lieu' => 'Nouveau lieu'
        ])->toArray();

        $response = $this->json('PUT', "/api/v2/exercices/{$exercice->id}", $updatedData);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);

        $this->assertDatabaseHas('exercices', [
            'id' => $exercice->id,
            'designation' => 'Nouveau nom',
            'lieu' => 'Nouveau lieu'
        ]);
    }

    /**
     * Test update exercice returns 404 when not found
     */
    public function testUpdateExerciceReturnsErrorWhenNotFound()
    {
        $updatedData = Exercice::factory()->make()->toArray();

        $response = $this->json('PUT', '/api/v2/exercices/99999', $updatedData);

        $response->assertStatus(404);
    }

    /**
     * Test validate exercice without sapeurs
     */
    public function testValidateExerciceWithoutSapeurs()
    {
        $exercice = Exercice::factory()->create();

        $response = $this->json('POST', "/api/v2/exercices/{$exercice->id}/valider");

        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => true
            ]);
    }

    /**
     * Test validate exercice successfully with sapeurs
     */
    public function testValidateExerciceSuccessfullyWithSapeurs()
    {
        $exercice = Exercice::factory()->create();
        $sapeur = Sapeur::factory()->create();

        $sapeurs = [
            [
                'sapeur_id' => $sapeur->id,
                'convoque' => 1,
                'present' => 1,
                'absent' => 0,
                'remplace' => 0,
                'amende' => 0,
                'excuse_type_id' => null,
                'excuse_statut' => 1,
            ],
        ];
        $this->json('POST', "/api/v2/exercices/{$exercice->id}/sapeurs", ['sapeurs' => $sapeurs]);

        $response = $this->json('POST', "/api/v2/exercices/{$exercice->id}/valider");

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**

     * Test delete exercice successfully
     */
    public function testDeleteExerciceSuccessfully()
    {
        $exercice = Exercice::factory()->create();

        $response = $this->json('DELETE', "/api/v2/exercices/{$exercice->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => 'success'
            ]);

        $this->assertDatabaseMissing('exercices', [
            'id' => $exercice->id
        ]);
    }

    /**
     * Test delete exercice returns 404 when not found
     */
    public function testDeleteExerciceReturns404WhenNotFound()
    {
        $response = $this->json('DELETE', "/api/v2/exercices/99999");

        // Returns 404 (probably from route model binding or middleware)
        $response->assertStatus(404);
    }
}
