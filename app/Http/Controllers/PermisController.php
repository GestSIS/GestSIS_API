<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PermisType;

class PermisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){

        $permis = PermisType::all();

        return response()->json(['data' => $permis]);
    }
}
