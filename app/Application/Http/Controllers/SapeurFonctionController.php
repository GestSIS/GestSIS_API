<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
use App\Domaine\Exceptions\ArrayException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurFonctionController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(int $sapeurId)
    {
        $fonctions = $this->service->getSapeurFonctionsById($sapeurId);

        return response()->json(['data' => $fonctions]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ArrayException
     */
    public function store(Request $request, int $sapeurId)
    {
        $data = $request->validate([
            'fonction_id' => 'required|integer|exists:fonctions,id',
            'debut' => 'required|date',
            'fin' => 'date|nullable|after_or_equal:debut',
            'remarque' => 'string|nullable',
        ]);

        $fonction = $this->service->addFonction($sapeurId, $data);

        return response()->json(['data' => $fonction]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $sapeurId
     * @param int $fonctionId
     * @return Response
     * @throws ArrayException
     */
    public function update(Request $request, int $sapeurId, int $fonctionId)
    {
        if ($fonctionId !== $request->get('id')) {
            return response()->json(['error' => 'invalid fonction id']);
        }

        $data = $request->validate([
            'id' => 'required|integer|exists:fonction_sapeur,id',
            'debut' => 'date',
            'fin' => 'date|nullable|after:debut',
            'remarque' => 'string|nullable',
        ]);

        $fonction = $this->service->updateFonction($sapeurId, $data);

        return response()->json(['data' => $fonction]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $sapeurId
     * @param int $fonctionId
     * @return Response
     */
    public function destroy(int $sapeurId, int $fonctionId)
    {
        $res = $this->service->removeFonction($sapeurId, $fonctionId);

        return response()->json(['data' => $res]);
    }

    public function fin(Request $request, int $sapeurId)
    {
        $data = $request->validate([
            'ids.*' => 'required|integer',
            'date' => 'required|date'
        ]);

        $fonctions = $this->service->finFonctions($sapeurId, $data['date'], $data['ids']);

        return response()->json(['data' => $fonctions]);
    }
}
