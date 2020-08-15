<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\Fonction;
use Illuminate\Http\Request;

class FonctionController extends Controller
{
/**
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */
    public function index()
    {
        //TODO Change this to use an extra level of indirections for consistency ???
        $fonctions = Fonction::all();

        return response()->json(['data' => $fonctions]);
    }
}
