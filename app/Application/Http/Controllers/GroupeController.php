<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\Groupe;
use Illuminate\Http\Request;

class GroupeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groupes = Groupe::all();

        return response()->json(['data' => $groupes]);
    }
}
