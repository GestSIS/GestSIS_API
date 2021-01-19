<?php

namespace Test\Feature;

use App\Domaine\API\ComptabiliteService;
use App\Domaine\API\SapeurService;
use App\Infrastructure\Models\Sapeur;
use Exception;
use Tests\TestCase;

class ImputationAnnuelTest extends TestCase
{

    protected $comptabiliteService;
    protected $sapeurOneId;
    protected $sapeurTwoId;
    protected $sapeurThreeId;
    protected $exerciceId;

    protected function setUp(): void
    {
        parent::setUp();

        $sapeurService = $this->app->make(SapeurService::class);
        $this->comptabiliteService = $this->app->make(ComptabiliteService::class);

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurOneId = $sapeurService->createSapeur($data)->id;
        $sapeurService->addFonction($this->sapeurOneId, [
            'fonction_id' => 1,
            'debut' => "1959-05-08",
            'fin' => null,
            'remarque' => 'Deserve it'
        ]);

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurTwoId = $sapeurService->createSapeur($data)->id;
        $sapeurService->addFonction($this->sapeurTwoId, [
            'fonction_id' => 4,
            'debut' => "1959-05-08",
            'fin' => null,
            'remarque' => 'Deserve it'
        ]);

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";
        $this->sapeurThreeId = $sapeurService->createSapeur($data)->id;
        $sapeurService->addFonction($this->sapeurThreeId, [
            'fonction_id' => 5,
            'debut' => "1959-05-08",
            'fin' => null,
            'remarque' => 'Deserve it'
        ]);

    }

    /**
     * Test add exercice
     *
     * @return void
     * @throws Exception
     */
    public function testImputationAnnuel()
    {
        $exercice_comptable_id = 3;

        $response = $this->json('POST', "/api/v2/imputation/annuel/$exercice_comptable_id");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);
    }

}
