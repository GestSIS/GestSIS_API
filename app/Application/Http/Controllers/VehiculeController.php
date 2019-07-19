<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\Vehicule;
use Illuminate\Http\Request;

class VehiculeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vehicules = Vehicule::all();

        return response()->json(['data' => $vehicules]);
    }
}
