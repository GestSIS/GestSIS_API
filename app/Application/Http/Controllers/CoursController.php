<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\Cours;
use Illuminate\Http\Request;

class CoursController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //TODO Change this to use an extra level of indirections for consistency ???
        $cours = Cours::all();

        return response()->json(['data' => $cours]);
    }
}
