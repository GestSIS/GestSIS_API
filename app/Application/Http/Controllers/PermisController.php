<?php

namespace App\Application\Http\Controllers;

use App\Models\PermisType;

class PermisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(['data' => PermisType::all()]);
    }
}
