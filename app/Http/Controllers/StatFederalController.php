<?php

namespace App\Http\Controllers;

use App\Models\StatFederal;
use Illuminate\Http\Response;

class StatFederalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $stats = StatFederal::all();

        return response()->json(['data' => $stats]);
    }
}
