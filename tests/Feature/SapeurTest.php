<?php

namespace Tests\Feature;

use App\Domaine\API\SapeurService;
use App\Infrastructure\Models\Sapeur;
use Exception;
use Tests\TestCase;

class SapeurTest extends TestCase
{

    protected $sapeurService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sapeurService = $this->app->make(SapeurService::class);
    }

    public function testIndexSapeur()
    {
        //Préparation
        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";

        $this->sapeurService->createSapeur($data);

        //TEST PART

        $response = $this->json('GET', '/api/v2/sapeurs');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'nom', 'prenom', 'fonction_id', 'actif']
                ]
            ]);
    }

    public function testShowSapeur()
    {
        //TEST PART

        $response = $this->json('GET', '/api/v2/sapeurs/1');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'nom', 'prenom', 'fonction_id', 'actif', 'date_naissance'
                ]
            ]);
    }

    public function testCreateSapeur()
    {
        //Préparation
        $data = Sapeur::factory()->make()->toArray();

        $data['incorporation'] = "29.01.2019";
        $data['date_naissance'] = "29.01.2019";

        //TEST PART

        $response = $this->json('POST', '/api/v2/sapeurs', $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'nom', 'prenom', 'date_naissance'
                ]
            ]);

        $sapeur = $response->getData()->data;

        foreach ($data as $key => $value) {
            if ($key !== "date_naissance" && $key !== "incorporation") {
                $this->assertTrue(get_object_vars($sapeur)[$key] == $value);
            }
        }

        $mutations = $this->sapeurService->getSapeurMutationsById($sapeur->id);
        $this->assertTrue(count($mutations) === 1);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     * @throws Exception
     */
    public function testUpdateSapeur()
    {
        //Préparation
        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";

        $sapeurId = $this->sapeurService->createSapeur($data)->id;
        $data = Sapeur::factory()->make()->toArray();
        $data['date_naissance'] = "1995-01-01";

        //TEST PART

        $response = $this->json('PUT', '/api/v2/sapeurs/' . $sapeurId, $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'nom', 'prenom', 'date_naissance'
                ]
            ]);

        $sapeur = $response->getData()->data;

        foreach ($data as $key => $value) {
            if ($key !== "date_naissance") {
                $this->assertTrue(get_object_vars($sapeur)[$key] == $value);
            }
        }
    }
}
