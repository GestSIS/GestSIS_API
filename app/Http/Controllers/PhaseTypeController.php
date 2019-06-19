<?php

namespace App\Http\Controllers;

use App\Models\PhaseType;
use Illuminate\Http\Response;

class PhaseTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $stats = PhaseType::all();

        return response()->json(['data' => $stats]);
    }
}
