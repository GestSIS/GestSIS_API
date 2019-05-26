<?php

namespace Tests\Unit;

use App\Models\Sapeur;
use App\Repository\SapeurBusiness;
use Carbon\Carbon;
use Exception;
use Tests\TestCase;

class SapeurCoursTest extends TestCase
{

    /**
     * Test add fonction
     *
     * @return void
     * @throws Exception
     */
    public function testAddCoursOK()
    {
        $id = 2;

        //Create fonction
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => null,
            'remarque' => '',
            'fonction_id' => 2
        );

        $sapeur = SapeurBusiness::get($id);
        $fonction_id = $sapeur->addFonction($data)->id;

        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'localite_id' => 1,
            'cours_id' => 2,
            'grade_id' => 5,
            'fonction_id' => 14,
            'fonction_sapeur_id' => $fonction_id,
        );

        $cours_id = $sapeur->addCours($data)->id;

        $fonction_old = Sapeur::find($id)->fonctions()->where('fonction_sapeur.id', $fonction_id)->first();
        $fonction_new = Sapeur::find($id)->fonctions()->where('fonction_id', $data['fonction_id'])->first();
        $grade = Sapeur::find($id)->grades()->where('grade_sapeur.grade_id', $data['grade_id'])->first();
        $cours = Sapeur::find($id)->cours()->where('cours_sapeur.id', $cours_id)->first();

        //Validate cours
        $this->assertTrue($data['date']->diffInDays($cours->date) === 0);
        $this->assertTrue($data['localite_id'] === $cours->localite_id);
        $this->assertTrue($data['cours_id'] === $cours->cours_id);

        //Validate new grade
        $this->assertTrue($data['date']->diffInDays($grade->date) === 0);

        //Validate old fonction
        $this->assertTrue($data['date']->diffInDays($fonction_old->fin) === 0);

        //Validate new fonction
        $this->assertTrue($data['date']->diffInDays($fonction_new->debut) === 0);
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
        $id = 2;
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'localite_id' => 1,
            'cours_id' => 2,
            'grade_id' => null,
            'fonction_id' => null,
            'fonction_sapeur_id' => null,
        );

        $sapeur = SapeurBusiness::get($id);
        $cours_id = $sapeur->addCours($data)->id;

        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'localite_id' => 2,
            'cours_sapeur_id' => $cours_id,
        );

        $sapeur->updateCours($data);

        $cours = Sapeur::find($id)->cours()->where('cours_sapeur.id', $cours_id)->first();

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
        $id = 2;
        $data = array(
            'date' => Carbon::createMidnightDate(1958, 1, 1),
            'localite_id' => 1,
            'cours_id' => 2,
            'grade_id' => null,
            'fonction_id' => null,
            'fonction_sapeur_id' => null,
        );

        $sapeur = SapeurBusiness::get($id);
        $cours_id = $sapeur->addCours($data)->id;

        $sapeur->removeCours($cours_id);

        $cours = Sapeur::find($id)->cours()->where('cours_sapeur.id', $cours_id)->first();
        $this->assertTrue($cours === null);
    }

}
