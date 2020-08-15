<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;
use App\Infrastructure\Models\Grade;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //TODO Change this to use an extra level of indirections for consistency ???
        $grades = Grade::all();

        return response()->json(['data' => $grades]);
    }
}
