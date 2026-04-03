<?php

namespace App\Application\Http\Controllers;

use App\Models\ControleMedical;

class SapeurControleMedicalController extends Controller
{
    public function index(int $sapeurId)
    {
        return response()->json(['data' => ControleMedical::where('sapeur_id', $sapeurId)->get()]);
    }
}
