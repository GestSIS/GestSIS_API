<?php

namespace Tests\Unit;

use App\Models\Sapeur;
use App\Repository\SapeurBusiness;
use Carbon\Carbon;
use Exception;
use Tests\TestCase;

class SapeurGradeTest extends TestCase
{
    /**
     * Test add grade
     *
     * @return void
     * @throws Exception
     */
    public function testAddGradeOK()
    {
        $id = 2;
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'remarque' => '',
            'grade_id' => 2
        );

        //Remove potential pre-existant grade
        Sapeur::find($id)->grades()->where('grade_sapeur.grade_id', $data['grade_id'])->delete();

        $sapeur = SapeurBusiness::get($id);
        $grade_id = $sapeur->addGrade($data)->id;

        $grade = Sapeur::find($id)->grades()->where('grade_sapeur.id', $grade_id)->first();

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
        $id = 2;
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'remarque' => '',
            'grade_id' => 2
        );

        //Remove potential pre-existant grade
        Sapeur::find($id)->grades()->where('grade_sapeur.grade_id', $data['grade_id'])->delete();

        $sapeur = SapeurBusiness::get($id);
        $sapeur->addGrade($data);

        try {
            $sapeur->addGrade($data);
            $this->assertTrue(false);
        } catch (Exception $e) {
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
        $id = 2;
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'remarque' => '',
            'grade_id' => 2
        );

        //Remove potential pre-existant grade
        Sapeur::find($id)->grades()->where('grade_sapeur.grade_id', $data['grade_id'])->delete();

        $sapeur = SapeurBusiness::get($id);
        $grade_id = $sapeur->addGrade($data)->id;

        $data = array(
            'id' => $grade_id,
            'date' => Carbon::createMidnightDate(1959, 5, 8),
            'remarque' => 'Deserve it'
        );

        $sapeur->updateGrade($data);

        $grade = Sapeur::find($id)->grades()->where('grade_sapeur.id', $grade_id)->first();

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
        $id = 2;
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'remarque' => '',
            'grade_id' => 2
        );

        //Remove potential pre-existant grade
        Sapeur::find($id)->grades()->where('grade_sapeur.grade_id', $data['grade_id'])->delete();

        $sapeur = SapeurBusiness::get($id);
        $grade_id = $sapeur->addGrade($data)->id;

        $sapeur->removeGrade($grade_id);

        $grade = Sapeur::find($id)->grades()->where('grade_sapeur.grade_id', $data['grade_id'])->first();

        $this->assertTrue($grade === null);
    }
}
