<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sapeur;

class SapeurFonctionController extends Controller
{
 /**
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */
    public function index($sapeur_id)
    {
        $fonctions = Sapeur::find($sapeur_id)->fonctions()->get();

        return response()->json(['data' => $fonctions]);
    }
}
