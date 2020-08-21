<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\ControleMedicalType;
use Illuminate\Http\Request;

class ControleMedicalTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        $types = ControleMedicalType::all();

        return response()->json(['data' => $types]);
    }
}
