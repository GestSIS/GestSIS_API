<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use Illuminate\Http\Request;

class FraisIndemniteAnnuelTypeController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $indemnites = $this->service->fraisIndemnitesAnnuel();

        return response()->json(['data' => $indemnites]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'compte_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',
            'cumulable' => 'boolean',
            'type' => 'integer|required',
        ]);

        $indemnite = $this->service->ajouterFraisIndemniteAnnuelType($data);
        return response()->json(['data' => $indemnite]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'compte_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',
            'cumulable' => 'boolean',
            'type' => 'integer|required',
        ]);

        $indemnite = $this->service->modifierFraisIndemniteAnnuelType($id, $data);
        return response()->json(['data' => $indemnite]);
    }

    public function destroy($id)
    {
        $indemnite = $this->service->supprimerFraisIndemniteAnnuelType($id);
        return response()->json(['data' => $indemnite]);
    }
}
