<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MatPersoService;
use Illuminate\Http\Request;

class MatPersoAttributionController extends Controller
{
    protected $service;

    public function __construct(MatPersoService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function attribuer(Request $request)
    {
        $data = $request->validate([
            'materiel.*.sapeurId' => 'required|integer',
            'materiel.*.date' => 'required|date',
            'materiel.*.id' => 'required|integer',
            'materiel.*.quantite' => 'nullable|integer',
        ]);

        $materiels = $this->service->attribuer($data['materiel']);

        return response()->json(['data' => $materiels]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function retour(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'materielIds' => 'string|min:1|max:11',
        ]);

        $materiels = $this->service->materiels();

        return response()->json(['data' => $materiels]);
    }
}
