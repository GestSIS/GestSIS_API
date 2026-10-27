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
     * La suppression d'une excuse doit réussir pour un sapeur convoqué et excusé
     * (le sapeur est renvoyé au même format que les autres endpoints de présence,
     * avec ses fiches de travail liées dans `travaux`).
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveExcuseReturnsSapeurWithTravaux()
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

        $response->assertStatus(204);
    }
}
