<?php

namespace App\Http\Controllers;

use App\Models\TypeIntervention;
use Illuminate\Http\Response;

class TypeInterventionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $types = TypeIntervention::all();

        return response()->json(['data' => $types]);
    }
}
