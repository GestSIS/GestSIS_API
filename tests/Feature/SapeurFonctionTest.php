<?php

namespace Tests\Unit;

use App\Domaine\API\SapeurService;
use App\Infrastructure\Models\Sapeur;
use Carbon\Carbon;
use Exception;
use Tests\TestCase;

class SapeurFonctionTest extends TestCase
{

    protected $service;
    protected $sapeurId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(SapeurService::class);

        $data = Sapeur::factory()->make()->toArray();
        $data['incorporation'] = "29.01.2019";

        $this->sapeurId = $this->service->createSapeur($data)->id;
    }

    /**
     * Test index fonction
     *
     * @return void
     * @throws Exception
     */
    public function testFonctionIndexOK()
    {
        $response = $this->json('GET', "/api/v2/sapeurs/1/fonctions");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'fonction_id', 'sapeur_id', 'debut', 'fin'
                    ]
                ]
            ]);
    }

    /**
     * Test add fonction
     *
     * @return void
     * @throws Exception
     */
    public function testAddFonctionOK()
    {
        $data = array(
            'debut' => "1958-01-01",
            'fin' => "1958-09-17",
            'remarque' => '',
            'fonction_id' => 2
        );

        $response = $this->json('POST', "/api/v2/sapeurs/$this->sapeurId/fonctions", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'fonction_id', 'sapeur_id', 'debut', 'fin'
                ]
            ]);

        $fonction = $response->getData()->data;

        $this->assertTrue($fonction !== null);
        $this->assertTrue(Carbon::parse($data['debut'])->diffInDays($fonction->debut) === 0);
        $this->assertTrue(Carbon::parse($data['fin'])->diffInDays($fonction->fin) === 0);
        $this->assertTrue($data['remarque'] === $fonction->remarque);
        $this->assertTrue($data['fonction_id'] === $fonction->fonction_id);
    }

    /**
     * Test duplicated fonction add
     *
     * @return void
     * @throws Exception
     */
    public function testAddFonctionDuplicated()
    {
        $data = array(
            'debut' => "1958-01-01",
            'fin' => "1958-09-17",
            'remarque' => '',
            'fonction_id' => 2
        );

        $this->service->addFonction($this->sapeurId, $data);

        $response = $this->json('POST', "/api/v2/sapeurs/$this->sapeurId/fonctions", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'error'
            ]);
    }

    /**
     * Test edit fonction
     *
     * @return void
     * @throws Exception
     */
    public function testEditFonction()
    {
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => Carbon::createMidnightDate(1958, 9, 17),
            'remarque' => '',
            'fonction_id' => 2
        );

        $fonction_id = $this->service->addFonction($this->sapeurId, $data)->id;

        $data = array(
            'id' => $fonction_id,
            'debut' => "1959-05-08",
            'fin' => "1960-09-17",
            'remarque' => 'Deserve it'
        );

        $response = $this->json('PUT', "/api/v2/sapeurs/$this->sapeurId/fonctions/$fonction_id", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'fonction_id', 'sapeur_id', 'debut', 'fin'
                ]
            ]);

        $fonction = $response->getData()->data;

        $this->assertTrue(Carbon::parse($data['debut'])->diffInDays($fonction->debut) === 0);
        $this->assertTrue(Carbon::parse($data['fin'])->diffInDays($fonction->fin) === 0);
        $this->assertTrue($data['remarque'] === $fonction->remarque);
    }

    /**
     * Test edit fonction
     *
     * @return void
     * @throws Exception
     */
    public function testEditFonctionInvalid()
    {
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => Carbon::createMidnightDate(1958, 9, 17),
            'remarque' => '',
            'fonction_id' => 2
        );

        $fonction_id = $this->service->addFonction($this->sapeurId, $data)->id;

        $data = array(
            'id' => $fonction_id,
            'debut' => "1959-05-08",
            'fin' => "1960-09-17",
            'remarque' => 'Deserve it'
        );

        $response = $this->json('PUT', "/api/v2/sapeurs/$this->sapeurId/fonctions/0", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'error'
            ]);
    }

    /**
     * Test remove fonction
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveFonction()
    {
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => Carbon::createMidnightDate(1958, 9, 17),
            'remarque' => '',
            'fonction_id' => 2
        );

        $fonction_id = $this->service->addFonction($this->sapeurId, $data)->id;

        $response = $this->json('DELETE', "/api/v2/sapeurs/$this->sapeurId/fonctions/$fonction_id");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);

        $fonctions = $this->service->getSapeurFonctionsById($this->sapeurId);
        array_filter($fonctions, function ($p) use ($fonction_id) {
            return $p->id == $fonction_id;
        });

        $this->assertTrue(count($fonctions) === 0);
    }


    /**
     * Test remove fonction
     *
     * @return void
     * @throws Exception
     */
    public function testFinFonctions()
    {
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'remarque' => '',
            'fonction_id' => 2
        );

        $fonction_id = $this->service->addFonction($this->sapeurId, $data)->id;

        $data = array(
            "date" => "1960-09-17",
            "ids" => array($fonction_id)
        );

        $response = $this->json('POST', "/api/v2/sapeurs/$this->sapeurId/fin-fonctions/", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);

        $fonctions = $this->service->getSapeurFonctionsById($this->sapeurId);
        array_filter($fonctions, function ($p) use ($fonction_id) {
            return $p->id == $fonction_id;
        });

        $this->assertTrue(!array_key_exists('fin', $fonctions));
    }
}
