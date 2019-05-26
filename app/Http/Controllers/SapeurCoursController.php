<?php

namespace App\Http\Controllers;

use App\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurCoursController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($sapeur_id)
    {
        $cours = Sapeur::find($sapeur_id)->cours()->get();

        return response()->json(['data' => $cours]);
    }

    //TODO controller edit group
}
