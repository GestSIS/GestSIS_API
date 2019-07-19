<?php

namespace Tests\Unit;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Sapeur;
use App\Domaine\API\SapeurService;
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

        $data = factory(Sapeur::class)->make()->toArray();
        $data['incorporation'] = "29.01.2019";

        $this->sapeurId = $this->service->createSapeur($data)->id;
    }

    /**
     * Test add grade
     *
     * @return void
     * @throws Exception
     */
    public function testAddGradeOK()
    {
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'remarque' => '',
            'grade_id' => 2
        );

        //Remove potential pre-existant grade
        Sapeur::find($this->sapeurId)->grades()->where('grade_sapeur.grade_id', $data['grade_id'])->delete();

        $grade_id = $this->service->addGrade($this->sapeurId, $data)->id;

        $grade = Sapeur::find($this->sapeurId)->grades()->where('grade_sapeur.id', $grade_id)->first();

        $this->assertTrue($grade !== null);
        $this->assertTrue($data['date']->diffInDays($grade->date) === 0);
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
            'grade_id' => 2
        );

        //Remove potential pre-existant grade
        Sapeur::find($this->sapeurId)->grades()->where('grade_sapeur.grade_id', $data['grade_id'])->delete();

        $this->service->addGrade($this->sapeurId, $data);

        try {
            $this->service->addGrade($this->sapeurId, $data);
            $this->assertTrue(false);
        } catch (ArrayException $e) {
            $this->assertTrue(true);
        }
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
            'grade_id' => 2
        );

        //Remove potential pre-existant grade
        Sapeur::find($this->sapeurId)->grades()->where('grade_sapeur.grade_id', $data['grade_id'])->delete();

        $grade_id = $this->service->addGrade($this->sapeurId, $data)->id;

        $data = array(
            'id' => $grade_id,
            'date' => Carbon::createMidnightDate(1959, 5, 8),
            'remarque' => 'Deserve it'
        );

        $this->service->updateGrade($this->sapeurId, $data);

        $grade = Sapeur::find($this->sapeurId)->grades()->where('grade_sapeur.id', $grade_id)->first();

        $this->assertTrue($data['date']->diffInDays($grade->date) === 0);
        $this->assertTrue($data['remarque'] === $grade->remarque);
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
            'grade_id' => 2
        );

        //Remove potential pre-existant grade
        Sapeur::find($this->sapeurId)->grades()->where('grade_sapeur.grade_id', $data['grade_id'])->delete();

        $grade_id = $this->service->addGrade($this->sapeurId, $data)->id;

        $this->service->removeGrade($this->sapeurId, $grade_id);

        $grade = Sapeur::find($this->sapeurId)->grades()->where('grade_sapeur.grade_id', $data['grade_id'])->first();

        $this->assertTrue($grade === null);
    }
}
