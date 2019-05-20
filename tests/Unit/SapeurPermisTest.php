<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Repository\SapeurBusiness;
use App\Models\Sapeur;

use Carbon\Carbon;
use Exception;

class SapeurPermisTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testAddPermisOK()
    {
        $id = 2;
        $permis_type = 9;

        //Remove potential pre-existant permis
        Sapeur::find($id)->permis()->where('permis_type_id',$permis_type)->delete();

        $sapeur = SapeurBusiness::get($id);
        $sapeur->addPermis(['permis_type_id'=>$permis_type, 'date' => Carbon::parse('1958-01-01')]);

        $permis = Sapeur::find($id)->permis()->where('permis_type_id',$permis_type)->first();
        $this->assertTrue($permis !== null);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testAddPermisDuplicated()
    {
        $id = 2;
        $permis_type = 9;

        //Remove potential pre-existant permis
        Sapeur::find($id)->permis()->where('permis_type_id',$permis_type)->delete();

        $sapeur = SapeurBusiness::get($id);
        $sapeur->addPermis(['permis_type_id'=>$permis_type, 'date' => Carbon::parse('1958-01-01')]);
        try{
            $sapeur->addPermis(['permis_type_id'=>$permis_type, 'date' => Carbon::parse('1958-01-01')]);
            $this->assertTrue(false);
        }catch(Exception $e){
            $this->assertTrue(true);
        }

        $permis = Sapeur::find($id)->permis()->where('permis_type_id',$permis_type)->first();
        $this->assertTrue($permis !== null);
    }
}
