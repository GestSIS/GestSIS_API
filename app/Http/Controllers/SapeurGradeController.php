<?php

namespace App\Http\Controllers;

use App\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurGradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($sapeur_id)
    {
        $grades = Sapeur::find($sapeur_id)->grades()->get();

        return response()->json(['data' => $grades]);
    }
}
