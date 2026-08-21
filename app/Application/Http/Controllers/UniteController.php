<?php

namespace App\Application\Http\Controllers;

use App\Models\TypeUnite;

class UniteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(['data' => TypeUnite::all()]);
    }
}
