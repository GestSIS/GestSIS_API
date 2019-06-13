<?php

namespace Tests\Unit;

use App\Models\Exercice;
use App\Repository\ExerciceBusiness;
use Carbon\Carbon;
use Exception;
use Tests\TestCase;

class ExerciceTest extends TestCase
{
    /**
     * Test add grade
     *
     * @return void
     * @throws Exception
     */
    public function testAddExerciceOK()
    {
        $data = array(
            'date' => Carbon::createMidnightDate(2019, 1, 12),
            'heure' => '19:30',
            'lieu' => 'Hangar',
            'communication' => 'Exercice PR n°1',
            'designation' => '-',
            'duree' => 120,
            'status' => '1',
            'exercice_categorie_id' => '1',
            'localite_id' => '7',
            'exercice_comptable_id' => '1'
        );

        $exercice = ExerciceBusiness::createExercice($data)->getData();

        $this->assertTrue($exercice !== null);
        $this->assertTrue($data['date']->diffInDays($exercice->date) === 0);
        $this->assertTrue($data['heure'] === $exercice->heure);
        $this->assertTrue($data['lieu'] === $exercice->lieu);
        $this->assertTrue($data['communication'] === $exercice->communication);
        $this->assertTrue($data['designation'] === $exercice->designation);
        $this->assertTrue($data['duree'] === $exercice->duree);
        $this->assertTrue($data['status'] === $exercice->status);
        $this->assertTrue($data['exercice_categorie_id'] === $exercice->exercice_categorie_id);
        $this->assertTrue($data['localite_id'] === $exercice->localite_id);
        $this->assertTrue($data['exercice_comptable_id'] === $exercice->exercice_comptable_id);
    }

    /**
     * Test edit grade
     *
     * @return void
     * @throws Exception
     */
    public function testEditExercice()
    {
        $id = 2;
        $data = array(
            'date' => Carbon::createMidnightDate(2019, 1, 12),
            'heure' => '19:30',
            'lieu' => 'Hangar',
            'communication' => 'Exercice PR n°1',
            'designation' => '-',
            'duree' => 120,
            'status' => '1',
            'exercice_categorie_id' => '1',
            'localite_id' => '7',
        );

        $exercice = ExerciceBusiness::get($id);
        $exercice = $exercice->update($data);

        $this->assertTrue($exercice !== null);
        $this->assertTrue($data['date']->diffInDays($exercice->date) === 0);
        $this->assertTrue($data['heure'] === $exercice->heure);
        $this->assertTrue($data['lieu'] === $exercice->lieu);
        $this->assertTrue($data['communication'] === $exercice->communication);
        $this->assertTrue($data['designation'] === $exercice->designation);
        $this->assertTrue($data['duree'] === $exercice->duree);
        $this->assertTrue($data['status'] === $exercice->status);
        $this->assertTrue($data['exercice_categorie_id'] === $exercice->exercice_categorie_id);
        $this->assertTrue($data['localite_id'] === $exercice->localite_id);
    }

    /**
     * Test remove grade
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveExercice()
    {
        $id = 6;

        ExerciceBusiness::delete($id);
        $exercice = Exercice::find($id);

        $this->assertTrue($exercice === null);
    }
}
