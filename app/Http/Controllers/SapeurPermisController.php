<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sapeur;

class SapeurPermisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($sapeur_id)
    {
        $permis = Sapeur::find($sapeur_id)->first()->permis()->with('PermisType')->get();

        return response()->json(['data' => $permis]);
    }
}
