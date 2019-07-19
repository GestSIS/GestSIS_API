<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\ExerciceComptable;
use Illuminate\Http\Request;

class ExerciceComptableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $exerciceComptables= ExerciceComptable::all();

        return response()->json(['data' => $exerciceComptables]);
    }
}
