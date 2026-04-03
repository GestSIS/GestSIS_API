<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\Sapeur;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ImputationExerciceTest extends TestCase
{
    use DatabaseTransactions;

    protected $sapeurOneId;
    protected $sapeurTwoId;
    protected $sapeurThreeId;
    protected $exerciceId;

    protected function setUp(): void
    {
        parent::setUp();

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['type'] = 0;
        $this->sapeurOneId = $this->json('POST', '/api/v2/sapeurs', $data)->json('data.id');

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['type'] = 0;
        $this->sapeurTwoId = $this->json('POST', '/api/v2/sapeurs', $data)->json('data.id');

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "2019-01-29";
        $data['type'] = 0;
        $this->sapeurThreeId = $this->json('POST', '/api/v2/sapeurs', $data)->json('data.id');

        $exerciceData = Exercice::factory()->make()->toArray();
        $this->exerciceId = $this->json('POST', '/api/v2/exercices', $exerciceData)->json('data.id');

        $this->json('POST', "/api/v2/exercices/{$this->exerciceId}/sapeurs", ['sapeurs' => [
            [
                'sapeur_id' => $this->sapeurOneId,
                'convoque' => 1,
                'present' => 1,
                'absent' => 0,
                'remplace' => 0,
                'amende' => 0,
                'excuse_type_id' => null,
                'excuse_statut' => 1,
            ],
            [
                'sapeur_id' => $this->sapeurTwoId,
                'convoque' => 1,
                'present' => 0,
                'absent' => 0,
                'remplace' => 0,
                'amende' => 0,
                'excuse_type_id' => 4,
                'excuse_statut' => -2,
            ],
            [
                'sapeur_id' => $this->sapeurThreeId,
                'convoque' => 1,
                'present' => 1,
                'absent' => 0,
                'remplace' => 0,
                'amende' => 0,
                'excuse_type_id' => null,
                'excuse_statut' => -1,
            ],
        ]]);
        $this->json('POST', "/api/v2/exercices/{$this->exerciceId}/valider");
    }

    /**
     * Test add exercice
     *
     * @return void
     * @throws Exception
     */
    public function testImputationExerciceParPiece()
    {
        $param = array(
            "indemnite_exercice_type_id" => 1
        );

        $response = $this->json('POST', '/api/v2/imputation/exercice/' . $this->exerciceId, $param);
        $response->assertStatus(200);
        $this->assertTrue(count($response->json('data.ecritures') ?? []) === 4);
    }

    /**
     * Test add exercice
     *
     * @return void
     * @throws Exception
     */
    public function testImputationExerciceParHeureEtFonction()
    {
        $param = array(
            "indemnite_exercice_type_id" => 10
        );

        $response = $this->json('POST', '/api/v2/imputation/exercice/' . $this->exerciceId, $param);
        $response->assertStatus(200);
        $this->assertTrue(count($response->json('data.ecritures')) === 2);
    }

    /**
     * Test add exercice
     *
     * @return void
     * @throws Exception
     */
    public function testImputationExerciceParHeureEtSoldeMin()
    {
        $param = array(
            "indemnite_exercice_type_id" => 2
        );

        $response = $this->json('POST', '/api/v2/imputation/exercice/' . $this->exerciceId, $param);
        $response->assertStatus(200);
        $this->assertTrue(count($response->json('data.ecritures')) === 2);
    }
}
