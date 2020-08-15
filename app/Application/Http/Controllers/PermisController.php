<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Infrastructure\Models\PermisType;

class PermisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        //TODO Change this to use an extra level of indirections for consistency ???
        $permis = PermisType::all();

        return response()->json(['data' => $permis]);
    }
}
