<?php

namespace App\Http\Controllers;

use App\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurMutationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($sapeur_id)
    {
        $mutations = Sapeur::find($sapeur_id)->mutations()->get();

        return response()->json(['data' => $mutations]);
    }
}
