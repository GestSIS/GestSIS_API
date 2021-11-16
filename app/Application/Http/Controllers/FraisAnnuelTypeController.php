<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use Illuminate\Http\Request;

class FraisAnnuelTypeController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $frais = $this->service->fraisAnnuel();

        return response()->json(['data' => $frais]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'compte_id' => 'integer',
            'ecriture_categorie_id' => 'integer',
            'cumulable' => 'boolean',
        ]);

        $frais = $this->service->ajouterFraisAnnuelType($data);
        return response()->json(['data' => $frais]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'compte_id' => 'integer',
            'ecriture_categorie_id' => 'integer',
            'cumulable' => 'boolean',
        ]);

        $frais = $this->service->modifierFraisAnnuelType($id, $data);
        return response()->json(['data' => $frais]);
    }

    public function destroy($id)
    {
        $this->service->supprimerFraisAnnuelType($id);
        return response()->json(['data' => 'ok']);
    }
}
