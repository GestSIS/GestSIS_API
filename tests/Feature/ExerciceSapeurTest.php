<?php

namespace Tests\Feature;

use App\Models\Exercice;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExerciceSapeurTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test index exercices
     *
     * @return void
     * @throws Exception
     */
    public function testExerciceIndexSapeurOk()
    {
        $response = $this->json('GET', "/api/v2/exercices/1/sapeurs");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'sapeur_id',
                        'exercice_id'
                    ]
                ]
            ]);
    }

    /**
     * Test add grade
     *
     * @return void
     * @throws Exception
     */
    public function testAddExerciceSapeurs()
    {
        $exercice = Exercice::factory()->create();

        $sapeurs = [
            'sapeurs' => [
                [
                    'sapeur_id' => 1,
                    'convoque' => 1,
                    'present' => 1,
                    'absent' => 0,
                    'remplace' => 0,
                    'amende' => 0,
                    'excuse_type_id' => null,
                    'excuse_statut' => 0,
                ],
                [
                    'sapeur_id' => 2,
                    'convoque' => 1,
                    'present' => 0,
                    'absent' => 0,
                    'remplace' => 0,
                    'amende' => 0,
                    'excuse_type_id' => 4,
                    'excuse_statut' => -2,
                ],
                [
                    'sapeur_id' => 3,
                    'convoque' => 1,
                    'present' => 0,
                    'absent' => 0,
                    'remplace' => 0,
                    'amende' => 0,
                    'excuse_type_id' => null,
                    'excuse_statut' => -1,
                ],
            ]
        ];

        $response = $this->json('POST', '/api/v2/exercices/' . $exercice->id . '/sapeurs', $sapeurs);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test edit grade
     *
     * @return void
     * @throws Exception
     */
    public function testEditExerciceSapeurs()
    {
        $exerciceData = Exercice::factory()->make()->toArray();
        $exerciceId = $this->json('POST', '/api/v2/exercices', $exerciceData)->json('data.id');

        $sapeurs = [
            [
                'sapeur_id' => 1,
                'convoque' => 1,
                'present' => 1,
                'absent' => 0,
                'remplace' => 0,
                'amende' => 0,
                'excuse_type_id' => null,
                'excuse_statut' => 0,
            ],
            [
                'sapeur_id' => 2,
                'convoque' => 1,
                'present' => 0,
                'absent' => 0,
                'remplace' => 0,
                'amende' => 0,
                'excuse_type_id' => 4,
                'excuse_statut' => -2,
            ],
            [
                'sapeur_id' => 3,
                'convoque' => 1,
                'present' => 0,
                'absent' => 0,
                'remplace' => 0,
                'amende' => 0,
                'excuse_type_id' => null,
                'excuse_statut' => 0,
            ]
        ];

        $addResponse = $this->json('POST', '/api/v2/exercices/' . $exerciceId . '/sapeurs', ['sapeurs' => $sapeurs]);
        $sapeurs = $addResponse->json('data.sapeurs');

        $sapeurs[1]['present'] = 0;
        $sapeurs[1]['excuse_type_id'] = 1;

        $response = $this->json('POST', '/api/v2/exercices/presence/' . $sapeurs[1]['id'], ["sapeurs" => $sapeurs[1]]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Test remove grade
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveExerciceSapeurs()
    {
        $exerciceData = Exercice::factory()->make()->toArray();
        $exerciceId = $this->json('POST', '/api/v2/exercices', $exerciceData)->json('data.id');

        $sapeurs = [
            [
                'sapeur_id' => 1,
                'convoque' => 1,
                'present' => 1,
                'absent' => 0,
                'remplace' => 0,
                'amende' => 0,
                'excuse_type_id' => null,
                'excuse_statut' => 0,
            ],
            [
                'sapeur_id' => 2,
                'convoque' => 1,
                'present' => 0,
                'absent' => 0,
                'remplace' => 0,
                'amende' => 0,
                'excuse_type_id' => 4,
                'excuse_statut' => -2,
            ],
            [
                'sapeur_id' => 3,
                'convoque' => 1,
                'present' => 0,
                'absent' => 0,
                'remplace' => 0,
                'amende' => 0,
                'excuse_type_id' => null,
                'excuse_statut' => 0,
            ]
        ];

        $addResponse = $this->json('POST', '/api/v2/exercices/' . $exerciceId . '/sapeurs', ['sapeurs' => $sapeurs]);
        $sapeurs = $addResponse->json('data.sapeurs');

        $ids = array_map(function ($sap) {
            return $sap['id'];
        }, $sapeurs);

        $response = $this->json('DELETE', '/api/v2/exercices/' . $exerciceId . '/sapeurs/', ["sapeurs" => $ids]);
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    /**
     * Un exercice validé (statut 3) mais pas encore imputé (statut 4) doit
     * rester modifiable : seule l'imputation bloque la suppression de
     * sapeurs (régression : le seuil utilisait EXERCICE_STATUT_SAISI au lieu
     * de EXERCICE_STATUT_VALIDE).
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveExerciceSapeursAllowedWhenExerciceValide()
    {
        $exerciceData = Exercice::factory()->make()->toArray();
        $exerciceId = $this->json('POST', '/api/v2/exercices', $exerciceData)->json('data.id');

        $sapeurs = [[
            'sapeur_id' => 1,
            'convoque' => 1,
            'present' => 1,
            'absent' => 0,
            'remplace' => 0,
            'amende' => 0,
            'excuse_type_id' => null,
            'excuse_statut' => 0,
        ]];

        $addResponse = $this->json('POST', '/api/v2/exercices/' . $exerciceId . '/sapeurs', ['sapeurs' => $sapeurs]);
        $ids = array_map(fn($sap) => $sap['id'], $addResponse->json('data.sapeurs'));

        Exercice::find($exerciceId)->update(['statut' => 3]); // Validé

        $response = $this->json('DELETE', '/api/v2/exercices/' . $exerciceId . '/sapeurs/', ['sapeurs' => $ids]);

        $response->assertStatus(200)->assertJsonMissingPath('error');
    }

    /**
     * Un exercice imputé (statut 4) ne doit en revanche plus être modifiable.
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveExerciceSapeursBlockedWhenExerciceImpute()
    {
        $exerciceData = Exercice::factory()->make()->toArray();
        $exerciceId = $this->json('POST', '/api/v2/exercices', $exerciceData)->json('data.id');

        $sapeurs = [[
            'sapeur_id' => 1,
            'convoque' => 1,
            'present' => 1,
            'absent' => 0,
            'remplace' => 0,
            'amende' => 0,
            'excuse_type_id' => null,
            'excuse_statut' => 0,
        ]];

        $addResponse = $this->json('POST', '/api/v2/exercices/' . $exerciceId . '/sapeurs', ['sapeurs' => $sapeurs]);
        $ids = array_map(fn($sap) => $sap['id'], $addResponse->json('data.sapeurs'));

        Exercice::find($exerciceId)->update(['statut' => 4]); // Imputé

        $response = $this->json('DELETE', '/api/v2/exercices/' . $exerciceId . '/sapeurs/', ['sapeurs' => $ids]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('error.message', 'Impossible de modifier un exercice déjà imputé');
    }

    /**
     * ConvocationsController::supprimerConvocations (suppression en masse
     * des convocations d'un sapeur, ex. lors de sa désactivation) doit
     * suivre la même règle : autorisé jusqu'à Validé(3), bloqué à
     * Imputé(4) — même régression que testRemoveExerciceSapeursAllowedWhenExerciceValide.
     *
     * @return void
     * @throws Exception
     */
    public function testSupprimerConvocationsAllowedWhenExerciceValide()
    {
        $exerciceData = Exercice::factory()->make()->toArray();
        $exerciceId = $this->json('POST', '/api/v2/exercices', $exerciceData)->json('data.id');

        $sapeurId = 1;
        $sapeurs = [[
            'sapeur_id' => $sapeurId,
            'convoque' => 1,
            'present' => 1,
            'absent' => 0,
            'remplace' => 0,
            'amende' => 0,
            'excuse_type_id' => null,
            'excuse_statut' => 0,
        ]];
        $this->json('POST', '/api/v2/exercices/' . $exerciceId . '/sapeurs', ['sapeurs' => $sapeurs]);

        Exercice::find($exerciceId)->update(['statut' => 3]); // Validé

        $response = $this->json(
            'POST',
            '/api/v2/sapeurs/' . $sapeurId . '/supprimer-convocations',
            ['convocations' => [$exerciceId]],
        );

        $response->assertStatus(200)->assertJsonMissingPath('error');
        $this->assertDatabaseMissing('exercice_sapeur', [
            'exercice_id' => $exerciceId,
            'sapeur_id' => $sapeurId,
        ]);
    }
}
