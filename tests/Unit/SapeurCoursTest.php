<?php

namespace Tests\Unit;

use App\Models\Sapeur;
use App\Services\SapeurService;
use Carbon\Carbon;
use Exception;
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

    /**
     * Test add fonction
     *
     * @return void
     * @throws Exception
     */
    public function testAddCoursOK()
    {

        //Create fonction
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => null,
            'remarque' => '',
            'fonction_id' => 2
        );

        $fonctionId = $this->service->addFonction($this->sapeurId, $data)->id;

        $data = array(
            'date' => Carbon::createMidnightDate(1958, 2, 7),
            'date_fonction' => Carbon::createMidnightDate(1960, 6, 5),
            'date_grade' => Carbon::createMidnightDate(1965, 12, 29),
            'localite_id' => 1,
            'cours_id' => 2,
            'grade_id' => 5,
            'fonction_id' => 14,
            'fonction_sapeur_id' => $fonctionId,
        );

        $coursId = $this->service->addCours($this->sapeurId, $data)->id;

        $fonction_old = Sapeur::find($this->sapeurId)->fonctions()->where('fonction_sapeur.id', $fonctionId)->first();
        $fonction_new = Sapeur::find($this->sapeurId)->fonctions()->where('fonction_id', $data['fonction_id'])->first();
        $grade = Sapeur::find($this->sapeurId)->grades()->where('grade_sapeur.grade_id', $data['grade_id'])->first();
        $cours = Sapeur::find($this->sapeurId)->cours()->where('cours_sapeur.id', $coursId)->first();

        //Validate cours
        $this->assertTrue($data['date']->diffInDays($cours->date) === 0);
        $this->assertTrue($data['localite_id'] === $cours->localite_id);
        $this->assertTrue($data['cours_id'] === $cours->cours_id);

        //Validate new grade
        $this->assertTrue($data['date_grade']->diffInDays($grade->date) === 0);

        //Validate old fonction
        $this->assertTrue($data['date_fonction']->diffInDays($fonction_old->fin) === 0);

        //Validate new fonction
        $this->assertTrue($data['date_fonction']->diffInDays($fonction_new->debut) === 0);
        $this->assertTrue($fonction_new->fin === null);

    }

    /**
     * Test edit fonction
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
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'localite_id' => 2,
            'id' => $coursId,
        );

        $this->service->updateCours($this->sapeurId, $data);

        $cours = Sapeur::find($this->sapeurId)->cours()->where('cours_sapeur.id', $coursId)->first();

        $this->assertTrue($data['date']->diffInDays($cours->date) === 0);
    }

    /**
     * Test remove fonction
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

        $this->service->removeCours($this->sapeurId, $coursId);

        $cours = Sapeur::find($this->sapeurId)->cours()->where('cours_sapeur.id', $coursId)->first();
        $this->assertTrue($cours === null);
    }

}
