<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Civilite;

class CiviliteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $civilites = Civilite::all();

        return response()->json(['data' => $civilites]);
    }
}
