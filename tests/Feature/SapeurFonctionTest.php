<?php

namespace Tests\Unit;

use App\Infrastructure\Models\Sapeur;
use App\Domaine\API\SapeurService;
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
    public function testAddFonctionOK()
    {
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => Carbon::createMidnightDate(1958, 9, 17),
            'remarque' => '',
            'fonction_id' => 2
        );

        //Remove potential pre-existant fonction
        Sapeur::find($this->sapeurId)->fonctions()->where('fonction_sapeur.fonction_id', $data['fonction_id'])->delete();

        $fonction_id = $this->service->addFonction($this->sapeurId, $data)->id;

        $fonction = Sapeur::find($this->sapeurId)->fonctions()->where('fonction_sapeur.id', $fonction_id)->first();

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
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => Carbon::createMidnightDate(1958, 9, 17),
            'remarque' => '',
            'fonction_id' => 2
        );

        //Remove potential pre-existant fonction
        Sapeur::find($this->sapeurId)->fonctions()->where('fonction_sapeur.fonction_id', $data['fonction_id'])->delete();

        $this->service->addFonction($this->sapeurId, $data);

        try {
            $this->service->addFonction($this->sapeurId, $data);
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
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => Carbon::createMidnightDate(1958, 9, 17),
            'remarque' => '',
            'fonction_id' => 2
        );

        //Remove potential pre-existant fonction
        Sapeur::find($this->sapeurId)->fonctions()->where('fonction_sapeur.fonction_id', $data['fonction_id'])->delete();

        $fonction_id = $this->service->addFonction($this->sapeurId, $data)->id;

        $data = array(
            'id' => $fonction_id,
            'debut' => Carbon::createMidnightDate(1959, 5, 8),
            'fin' => Carbon::createMidnightDate(1960, 9, 17),
            'remarque' => 'Deserve it'
        );

        $this->service->updateFonction($this->sapeurId, $data);

        $fonction = Sapeur::find($this->sapeurId)->fonctions()->where('fonction_sapeur.id', $fonction_id)->first();

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
        $data = array(
            'debut' => Carbon::createMidnightDate(1958, 1, 1),
            'fin' => Carbon::createMidnightDate(1958, 9, 17),
            'remarque' => '',
            'fonction_id' => 2
        );

        //Remove potential pre-existant fonction
        Sapeur::find($this->sapeurId)->fonctions()->where('fonction_sapeur.fonction_id', $data['fonction_id'])->delete();

        $fonction_id = $this->service->addFonction($this->sapeurId, $data)->id;

        $this->service->removeFonction($this->sapeurId, $fonction_id);

        $fonction = Sapeur::find($this->sapeurId)->fonctions()->where('fonction_sapeur.id', $fonction_id)->first();
        $this->assertTrue($fonction === null);
    }
}
