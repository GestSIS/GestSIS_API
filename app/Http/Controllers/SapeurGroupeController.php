<?php

namespace App\Http\Controllers;

use App\Models\Sapeur;
use App\Business\SapeurBusiness;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurGroupeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $groupes = Sapeur::find($id)->groupes()->get();

        return response()->json(['data' => $groupes]);
    }

}
