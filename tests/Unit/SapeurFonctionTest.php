<?php

namespace Tests\Unit;

use App\Models\Sapeur;
use App\Repository\SapeurBusiness;
use Carbon\Carbon;
use Exception;
use Tests\TestCase;

class SapeurFonctionTest extends TestCase
{
    /**
     * Test add fonction
     *
     * @return void
     * @throws Exception
     */
    public function testAddFonctionOK()
    {
        $id = 2;
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => Carbon::createMidnightDate(1958, 9, 17),
            'remarque' => '',
            'fonction_id' => 2
        );

        $sapeur = SapeurBusiness::get($id);
        $fonction_id = $sapeur->addFonction($data)->id;

        $fonction = Sapeur::find($id)->fonctions()->where('fonction_sapeur.id', $fonction_id)->first();

        $this->assertTrue($fonction !== null);
        $this->assertTrue($data['debut']->diffInDays($fonction->debut) === 0);
        $this->assertTrue($data['fin']->diffInDays($fonction->fin) === 0);
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
        $id = 2;
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => Carbon::createMidnightDate(1958, 9, 17),
            'remarque' => '',
            'fonction_id' => 2
        );

        //Remove potential pre-existant fonction
        Sapeur::find($id)->fonctions()->where('fonction_sapeur.fonction_id', $data['fonction_id'])->delete();

        $sapeur = SapeurBusiness::get($id);
        $sapeur->addFonction($data);

        try {
            $sapeur->addFonction($data);
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Test edit fonction
     *
     * @return void
     * @throws Exception
     */
    public function testEditFonction()
    {
        $id = 2;
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => Carbon::createMidnightDate(1958, 9, 17),
            'remarque' => '',
            'fonction_id' => 2
        );

        //Remove potential pre-existant fonction
        Sapeur::find($id)->fonctions()->where('fonction_sapeur.fonction_id', $data['fonction_id'])->delete();

        $sapeur = SapeurBusiness::get($id);
        $fonction_id = $sapeur->addFonction($data)->id;

        $data = array(
            'fonction_sapeur_id' => $fonction_id,
            'debut' => Carbon::createMidnightDate(1959, 5, 8),
            'fin' => Carbon::createMidnightDate(1960, 9, 17),
            'remarque' => 'Deserve it'
        );

        $sapeur->updateFonction($data);

        $fonction = Sapeur::find($id)->fonctions()->where('fonction_sapeur.id', $fonction_id)->first();

        $this->assertTrue($data['debut']->diffInDays($fonction->debut) === 0);
        $this->assertTrue($data['fin']->diffInDays($fonction->fin) === 0);
        $this->assertTrue($data['remarque'] === $fonction->remarque);
    }

    /**
     * Test remove fonction
     *
     * @return void
     * @throws Exception
     */
    public function testRemoveFonction()
    {
        $id = 2;
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => Carbon::createMidnightDate(1958, 9, 17),
            'remarque' => '',
            'fonction_id' => 2
        );

        //Remove potential pre-existant fonction
        Sapeur::find($id)->fonctions()->where('fonction_sapeur.fonction_id', $data['fonction_id'])->delete();

        $sapeur = SapeurBusiness::get($id);
        $fonction_id = $sapeur->addFonction($data)->id;

        $sapeur->removeFonction($fonction_id);

        $fonction = Sapeur::find($id)->fonctions()->where('fonction_sapeur.id', $fonction_id)->first();
        $this->assertTrue($fonction === null);
    }
}
