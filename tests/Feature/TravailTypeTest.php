<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TravailTypeTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @param array<string, mixed> $liens
     * @return array<string, mixed>
     */
    private function payload(array $liens = []): array
    {
        return [
            'designation' => 'Rédition du matériel',
            'actif' => true,
            'ecriture_categorie_id' => 1,
            'type_unite_id' => 2,
            'fonctions' => [
                ['type' => 1, 'tarif' => 30.00, 'compte_id' => 1, 'fonction_id' => null],
            ],
            ...$liens,
        ];
    }

    public function testStoreEnregistreLesLiens()
    {
        $response = $this->json('POST', '/api/v2/travail-types', $this->payload([
            'lien_exercice' => true,
            'lien_obligatoire' => true,
        ]));

        $response->assertStatus(201);
        $this->assertTrue($response->json('data.lien_exercice'));
        $this->assertFalse($response->json('data.lien_intervention'));
        $this->assertTrue($response->json('data.lien_obligatoire'));
    }

    public function testStoreLiensParDefautFaux()
    {
        $response = $this->json('POST', '/api/v2/travail-types', $this->payload());

        $response->assertStatus(201);
        $id = $response->json('data.id');
        $type = $this->json('GET', '/api/v2/travail-types')->collect('data')->firstWhere('id', $id);
        $this->assertFalse($type['lien_exercice']);
        $this->assertFalse($type['lien_intervention']);
        $this->assertFalse($type['lien_obligatoire']);
    }

    public function testStoreRefuseObligatoireSansLien()
    {
        $response = $this->json('POST', '/api/v2/travail-types', $this->payload([
            'lien_exercice' => false,
            'lien_intervention' => false,
            'lien_obligatoire' => true,
        ]));

        $response->assertStatus(422);
    }

    public function testUpdateRefuseObligatoireSansLien()
    {
        $id = $this->json('POST', '/api/v2/travail-types', $this->payload())->json('data.id');

        $response = $this->json('PUT', "/api/v2/travail-types/{$id}", $this->payload([
            'lien_obligatoire' => true,
        ]));

        $response->assertStatus(422);
    }
}
