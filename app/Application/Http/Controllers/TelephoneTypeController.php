<?php

namespace App\Application\Http\Controllers;

use App\Models\TelephoneType;
use Illuminate\Http\Request;

class TelephoneTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $types = TelephoneType::all();

        return response()->json(['data' => $types]);
    }
}
