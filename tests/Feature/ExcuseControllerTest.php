<?php

namespace Tests\Feature;

use App\Models\Exercice;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExcuseControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * La suppression d'une excuse doit retourner le sapeur avec ses heures,
     * au même format que les autres endpoints de présence, sinon le front
     * (ExerciceTabSapeurs.vue) plante sur `sap.heures.find(...)`.
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveExcuseReturnsSapeurWithHeures()
    {
        $exerciceData = Exercice::factory()->make()->toArray();
        $exerciceId = $this->json('POST', '/api/v2/exercices', $exerciceData)->json('data.id');

        $sapeurs = [
            [
                'sapeur_id' => 1,
                'convoque' => 1,
                'present' => 0,
                'absent' => 1,
                'remplace' => 0,
                'amende' => 0,
                'excuse_type_id' => 4,
                'excuse_statut' => 0,
            ],
        ];

        $this->json('POST', '/api/v2/exercices/' . $exerciceId . '/sapeurs', ['sapeurs' => $sapeurs]);

        $response = $this->json('DELETE', '/api/v2/exercices/' . $exerciceId . '/excuses/1');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'sapeur_id',
                    'exercice_id',
                    'excuse_type_id',
                    'heures',
                ],
            ])
            ->assertJson([
                'data' => [
                    'excuse_type_id' => null,
                ],
            ]);
    }
}
