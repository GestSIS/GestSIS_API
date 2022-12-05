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
            'depuisInventaire' => 'boolean',
            'attributions.*.sapeur_id' => 'required|integer',
            'attributions.*.date' => 'required|date',
            'attributions.*.id' => 'nullable|integer',
            'attributions.*.materiel_type_id' => 'nullable|integer|min:1',
            'attributions.*.quantite' => 'nullable|integer|min:1',
            'attributions.*.remarque' => 'nullable|string',
            'attributions.*.numero' => 'nullable|string',
            'attributions.*.achat' => 'nullable|string',
        ]);

        $materiels = $this->service->attribuer($data['attributions']);

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
            'materielIds.*' => 'integer|min:1',
        ]);

        $materiels = $this->service->retour($data['date'], $data['materielIds']);

        return response()->json(['data' => $materiels]);
    }
}
