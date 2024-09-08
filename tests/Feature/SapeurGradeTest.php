<?php

namespace Tests\Feature;

use App\Domaine\API\SapeurService;
use App\Infrastructure\Models\Sapeur;
use Carbon\Carbon;
use Exception;
use Tests\TestCase;

class SapeurGradeTest extends TestCase
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
     * Test add permis
     *
     * @return void
     * @throws Exception
     */
    public function testGradeIndexOk()
    {
        $response = $this->json('GET', "/api/v2/sapeurs/1/grades");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'grade_id', 'sapeur_id', 'date'
                    ]
                ]
            ]);
    }

    /**
     * Test index grade
     *
     * @return void
     * @throws Exception
     */
    public function testAddGradeOk()
    {
        $data = array(
            'date' => "1958-01-01",
            'remarque' => '',
            'grade_id' => 2
        );

        $response = $this->json('POST', "/api/v2/sapeurs/$this->sapeurId/grades", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['grade' => [
                    'id', 'grade_id', 'sapeur_id', 'date'
                ]]
            ]);

        $grade = $response->getData()->data->grade;

        $this->assertTrue($grade !== null);
        $this->assertTrue(Carbon::parse($data['date'])->diffInDays($grade->date) === 0.0);
        $this->assertTrue($data['remarque'] === $grade->remarque);
        $this->assertTrue($data['grade_id'] === $grade->grade_id);
    }

    /**
     * Test duplicated grade add
     *
     * @return void
     * @throws Exception
     */
    public function testAddGradeDuplicated()
    {
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'remarque' => '',
            'grade_id' => 3
        );

        $this->service->addGrade($this->sapeurId, $data);

        $response = $this->json('POST', "/api/v2/sapeurs/$this->sapeurId/grades", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'error'
            ]);
    }

    /**
     * Test edit grade
     *
     * @return void
     * @throws Exception
     */
    public function testEditGrade()
    {
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'remarque' => '',
            'grade_id' => 4
        );

        $grade_id = $this->service->addGrade($this->sapeurId, $data)['grade']->id;

        $data = array(
            'id' => $grade_id,
            'date' => "1959-05-08",
            'remarque' => 'Deserve it'
        );

        $response = $this->json('PUT', "/api/v2/sapeurs/$this->sapeurId/grades/$grade_id", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['grade' => [
                    'id', 'grade_id', 'sapeur_id', 'date'
                ]]
            ]);

        $grade = $response->getData()->data->grade;

        $this->assertTrue(Carbon::parse($data['date'])->diffInDays($grade->date) === 0.0);
        $this->assertTrue($data['remarque'] === $grade->remarque);
    }

    /**
     * Test edit grade
     *
     * @return void
     * @throws Exception
     */
    public function testEditGradeInvalid()
    {
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'remarque' => '',
            'grade_id' => 6
        );

        $grade_id = $this->service->addGrade($this->sapeurId, $data)['grade']->id;

        $data = array(
            'id' => $grade_id,
            'date' => "1959-05-08",
            'remarque' => 'Deserve it'
        );

        $response = $this->json('PUT', "/api/v2/sapeurs/$this->sapeurId/grades/0", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'error'
            ]);
    }

    /**
     * Test remove grade
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveGrade()
    {
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'remarque' => '',
            'grade_id' => 5
        );

        $grade_id = $this->service->addGrade($this->sapeurId, $data)['grade']->id;

        $response = $this->json('DELETE', "/api/v2/sapeurs/$this->sapeurId/grades/$grade_id");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);

        $grades = $this->service->getSapeurGradesById($this->sapeurId);
        array_filter($grades, function ($p) use ($grade_id) {
            return $p->id == $grade_id;
        });

        $this->assertTrue(count($grades) === 0);
    }
}
