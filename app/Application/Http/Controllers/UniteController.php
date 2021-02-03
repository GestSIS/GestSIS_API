<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\TypeUnite;

class UniteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //TODO Change this to use an extra level of indirections for consistency ???
        $unites = TypeUnite::all();

        return response()->json(['data' => $unites]);
    }
}
