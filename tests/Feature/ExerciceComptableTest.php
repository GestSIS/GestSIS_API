<?php

namespace Test\Feature;

use App\Domaine\API\ExerciceService;
use App\Infrastructure\Models\Exercice;
use Exception;
use Tests\TestCase;

class ExerciceComptableTest extends TestCase
{

    /**
     * Test index groupe
     *
     * @return void
     * @throws Exception
     */
    public function testExerciceComptableIndexOk()
    {
        $response = $this->json('GET', "/api/v2/exercice-comptables/");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'designation', 'annee', 'debut', 'fin'
                    ]
                ]
            ]);
    }


    /**
     * génération du certificat de salaire d'un sapeur
     *
     * @return void
     */
    public function certificatSalaireSapeur()
    {
        $response = $this->json('GET', "api/v2/exercice-comptables/2/certificat-salaire/3");

        $response->assertStatus(200);
    }


    /**
     * génération du certificat de salaire de tous les sapeurs
     *
     * @return void
     */
    public function certificatsSalaire()
    {
        $response = $this->json('GET', "api/v2/exercice-comptables/2/certificat-salaire");

        $response->assertStatus(200);
    }

}
