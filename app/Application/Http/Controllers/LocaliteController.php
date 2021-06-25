<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;
use App\Infrastructure\Models\Localite;

class LocaliteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $localites = Localite::all();

        return response()->json(['data' => $localites]);
    }
}
