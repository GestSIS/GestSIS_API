<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\Medecin;
use Illuminate\Http\Response;

class MedecinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function all()
    {
        $medecins = Medecin::all();

        return response()->json(['data' => $medecins]);
    }
}
