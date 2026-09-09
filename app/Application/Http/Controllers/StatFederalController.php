<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionParamBusiness;
use App\Models\StatFederal;
use Illuminate\Http\Request;

class StatFederalController extends Controller
{
    public function index()
    {
        $statsFederal = StatFederal::all();

        return response()->json(['data' => $statsFederal]);
    }
}
