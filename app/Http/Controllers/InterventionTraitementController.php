<?php

namespace App\Http\Controllers;

use App\Models\InterventionTraitement;
use Illuminate\Http\Response;

class InterventionTraitementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $traitements = InterventionTraitement::all();

        return response()->json(['data' => $traitements]);
    }

}
