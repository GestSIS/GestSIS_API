<?php

namespace Tests\Feature;

use App\Domaine\API\SapeurService;
use App\Infrastructure\Models\Sapeur;
use Carbon\Carbon;
use Tests\TestCase;

class SapeurCoursTest extends TestCase
{

    protected $service;
    protected $sapeurId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(SapeurService::class);

        $data = factory(Sapeur::class)->make()->toArray();
        $data['incorporation'] = "29.01.2019";

        $this->sapeurId = $this->service->createSapeur($data)->id;
    }

    public function testIndexSapeurCours()
    {
        $response = $this->json('GET', '/api/v2/sapeurs/1/cours');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'cours_id', 'sapeur_id', 'localite_id']
                ]
            ]);
    }

    /**
     * Test add cours
     *
     * @return void
     * @throws Exception
     */
    public function testAddCoursOK()
    {
        //Create cours
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => null,
            'remarque' => '',
            'fonction_id' => 2
        );

        $fonctionId = $this->service->addFonction($this->sapeurId, $data)->id;

        $data = array(
            'date' => "1958-02-07",
            'date_fonction' => "1960-06-05",
            'date_grade' => "1965-12-29",
            'localite_id' => 1,
            'cours_id' => 2,
            'grade_id' => 5,
            'fonction_id' => 14,
            'fonction_sapeur_id' => $fonctionId,
        );

        $response = $this->json(
            'POST',
            "/api/v2/sapeurs/$this->sapeurId/cours", $data
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'sapeur_id', 'cours_id', 'date', 'localite_id'
                ]
            ]);

        $cours = $response->getData()->data;

        $fonctions = $this->service->getSapeurFonctionsById($this->sapeurId);
        $grades = $this->service->getSapeurGradesById($this->sapeurId);
        $fonctions_old = array_filter($fonctions, function ($f) use ($fonctionId) {
            return $f->id === $fonctionId;
        });
        $fonction_old = array_pop($fonctions_old);
        $fonctions_new = array_filter($fonctions, function ($f) use ($data) {
            return $f->fonction_id === $data['fonction_id'];
        });
        $fonction_new = array_pop($fonctions_new);
        $grades = array_filter($grades, function ($g) use ($data) {
            return $g->grade_id === $data['grade_id'];
        });
        $grade = array_pop($grades);

        //Validate cours
        $this->assertTrue(Carbon::parse($data['date'])->diffInDays($cours->date) === 0);
        $this->assertTrue($data['localite_id'] === $cours->localite_id);
        $this->assertTrue($data['cours_id'] === $cours->cours_id);

        //Validate new grade
        $this->assertTrue(Carbon::parse($data['date_grade'])->diffInDays($grade->date) === 0);

        //Validate old fonction
        $this->assertTrue(Carbon::parse($data['date_fonction'])->diffInDays($fonction_old->fin) === 0);

        //Validate new fontion
        $this->assertTrue(Carbon::parse($data['date_fonction'])->diffInDays($fonction_new->debut) === 0);
        $this->assertTrue($fonction_new->fin === null);

    }

    /**
     * Test edit cours
     *
     * @return void
     * @throws Exception
     */
    public function testEditCours()
    {
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'date_fonction' => Carbon::createMidnightDate(1960, 1, 1),
            'date_grade' => Carbon::createMidnightDate(1965, 1, 1),
            'localite_id' => 1,
            'cours_id' => 2,
            'grade_id' => null,
            'fonction_id' => null,
            'fonction_sapeur_id' => null,
        );

        $coursId = $this->service->addCours($this->sapeurId, $data)->id;

        $data = array(
            'date' => "1958-01-01",
            'localite_id' => 2,
            'id' => $coursId,
        );

        $response = $this->json(
            'PUT',
            "/api/v2/sapeurs/$this->sapeurId/cours/$coursId", $data
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'sapeur_id', 'cours_id', 'date', 'localite_id'
                ]
            ]);

        $cours = $response->getData()->data;

        $this->assertTrue(Carbon::parse($data['date'])->diffInDays($cours->date) === 0);
    }

    /**
     * Test edit cours
     *
     * @return void
     * @throws Exception
     */
    public function testEditCoursInvalid()
    {
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'date_fonction' => Carbon::createMidnightDate(1960, 1, 1),
            'date_grade' => Carbon::createMidnightDate(1965, 1, 1),
            'localite_id' => 1,
            'cours_id' => 2,
            'grade_id' => null,
            'fonction_id' => null,
            'fonction_sapeur_id' => null,
        );

        $coursId = $this->service->addCours($this->sapeurId, $data)->id;

        $data = array(
            'date' => "1958-01-01",
            'localite_id' => 2,
            'id' => $coursId,
        );

        $response = $this->json(
            'PUT',
            "/api/v2/sapeurs/$this->sapeurId/cours/0", $data
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'error'
            ]);
    }

    /**
     * Test remove cours
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveCours()
    {
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'date_fonction' => Carbon::createMidnightDate(1960, 1, 1),
            'date_grade' => Carbon::createMidnightDate(1965, 1, 1),
            'localite_id' => 1,
            'cours_id' => 2,
            'grade_id' => null,
            'fonction_id' => null,
            'fonction_sapeur_id' => null,
        );

        $coursId = $this->service->addCours($this->sapeurId, $data)->id;

        $response = $this->json('DELETE', "/api/v2/sapeurs/$this->sapeurId/cours/$coursId");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);

        $cours = $this->service->getSapeurGradesById($this->sapeurId);
        array_filter($cours, function ($p) use ($coursId) {
            return $p->id == $coursId;
        });

        $this->assertTrue(count($cours) === 0);
    }

}
