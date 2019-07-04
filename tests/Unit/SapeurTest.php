<?php

namespace Tests\Feature;

use App\Models\Sapeur;
use App\Services\SapeurService;
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

    public function testCreateSapeur()
    {
        $data = factory(Sapeur::class)->make()->toArray();
        $data['incorporation'] = "29.01.2019";

        $sapeur = $this->sapeurService->createSapeur($data);

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
        $data = factory(Sapeur::class)->make()->toArray();
        $data['incorporation'] = "29.01.2019";

        $sapeurId = $this->sapeurService->createSapeur($data)->id;
        $data = factory(Sapeur::class)->make()->toArray();

        $sapeur = $this->sapeurService->editSapeurDetailsById($sapeurId, $data);

        foreach ($data as $key => $value) {
            if ($key !== "date_naissance") {
                $this->assertTrue(get_object_vars($sapeur)[$key] == $value);
            }
        }
    }
}
