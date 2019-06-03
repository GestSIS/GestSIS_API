<?php

namespace Tests\Unit;

use App\Models\ExerciceSapeur;
use App\Repository\ExerciceBusiness;
use Exception;
use Tests\TestCase;

class ExerciceSapeurTest extends TestCase
{
    /**
     * Test add grade
     *
     * @return void
     * @throws Exception
     */
    public function testAddExerciceSapeurs()
    {
        $id = 649;

        $data = array(
            'sapeurs' => array(
                array(
                    'sapeur_id' => 1,
                    'convoque' => 1,
                    'present' => 1,
                    'amende' => 0,
                    'remplace' => 0,
                    'excuse_type_id' => null
                ),
                array(
                    'sapeur_id' => 2,
                    'convoque' => 1,
                    'present' => 0,
                    'amende' => 1,
                    'remplace' => 0,
                    'excuse_type_id' => 4
                ),
                array(
                    'sapeur_id' => 3,
                    'convoque' => 1,
                    'present' => 0,
                    'amende' => 0,
                    'remplace' => 0,
                    'excuse_type_id' => null
                ),
            )
        );
        ExerciceSapeur::where('exercice_id', $id)->delete();

        $sapeurs = ExerciceBusiness::get($id)->addSapeurs($data);

        $this->assertTrue(count($sapeurs) === 3);
        foreach ($data['sapeurs'] as $sapeur) {
            $sap = array_values(array_filter(
                $sapeurs->toArray(),
                function ($e) use ($sapeur) {
                    return $e['sapeur_id'] === $sapeur['sapeur_id'];
                }
            ))[0];
            $this->assertTrue($sapeur['convoque'] === $sap['convoque']);
            $this->assertTrue($sapeur['present'] === $sap['present']);
            $this->assertTrue($sapeur['amende'] === $sap['amende']);
            $this->assertTrue($sapeur['remplace'] === $sap['remplace']);
            $this->assertTrue($sapeur['excuse_type_id'] === $sap['excuse_type_id']);
        }
    }

    /**
     * Test edit grade
     *
     * @return void
     * @throws Exception
     */
    public function testEditExerciceSapeurs()
    {
        $id = 649;
        $sapeur_id = 1;
        $sapeur_exercice = ExerciceSapeur::where('exercice_id', 649)->where('sapeur_id', $sapeur_id)->first();
        $data = array(
            'sapeurs' =>
                array(
                    array(
                        'id' => $sapeur_exercice->id,
                        'sapeur_id' => $sapeur_id,
                        'convoque' => 1,
                        'present' => 0,
                        'amende' => 0,
                        'remplace' => 1,
                        'excuse_type_id' => null,
                    ),
                )
        );

        $exercice = ExerciceBusiness::get($id);
        $sapeurs = $exercice->updateSapeurs($data);

        $this->assertTrue(count($sapeurs) === 3);
        foreach ($data['sapeurs'] as $sapeur) {
            $sap = array_values(array_filter(
                $sapeurs->toArray(),
                function ($e) use ($sapeur) {
                    return $e['sapeur_id'] === $sapeur['sapeur_id'];
                }
            ))[0];
            $this->assertTrue($sapeur['convoque'] === $sap['convoque']);
            $this->assertTrue($sapeur['present'] === $sap['present']);
            $this->assertTrue($sapeur['amende'] === $sap['amende']);
            $this->assertTrue($sapeur['remplace'] === $sap['remplace']);
            $this->assertTrue($sapeur['excuse_type_id'] === $sap['excuse_type_id']);
        }
    }

    /**
     * Test remove grade
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveExerciceSapeurs()
    {
        $id = 649;

        $data = array(
            'sapeurs' => array()
        );

        $sapeurs = ExerciceSapeur::where('exercice_id', $id)->get();
        foreach ($sapeurs as $sapeur) {
            if($sapeur->sapeur_id < 3){
                array_push($data['sapeurs'], $sapeur->id);
            }
        }

        ExerciceBusiness::get($id)->removeSapeurs($data);

        $sapeurs = ExerciceSapeur::where('exercice_id', $id)->get();
        $this->assertTrue(count($sapeurs->toArray()) === 1);
        $this->assertTrue($sapeurs[0]['sapeur_id'] === 3);
    }
}
