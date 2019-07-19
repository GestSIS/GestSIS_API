<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\Materiel;
use Illuminate\Http\Request;

class MaterielController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $materiel = Materiel::all();

        return response()->json(['data' => $materiel]);
    }
}
