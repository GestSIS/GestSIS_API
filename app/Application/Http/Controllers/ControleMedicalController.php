<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\ControleMedical;

class ControleMedicalController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
   public function all()
   {
       $controles = ControleMedical::all();

       return response()->json(['data' => $controles]);
   }
}
