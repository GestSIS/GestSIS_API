<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use Illuminate\Http\Request;

class IndemniteAnnuelTypeController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $indemnites = $this->service->indemnitesAnnuel();

        return response()->json(['data' => $indemnites]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'compte_id' => 'integer',
            'ecriture_categorie_id' => 'integer',
            'cumulable' => 'boolean',
        ]);

        $indemnite = $this->service->ajouterIndemniteAnnuelType($data);
        return response()->json(['data' => $indemnite]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'compte_id' => 'integer',
            'ecriture_categorie_id' => 'integer',
            'cumulable' => 'boolean',
        ]);

        $indemnite = $this->service->modifierIndemniteAnnuelType($id, $data);
        return response()->json(['data' => $indemnite]);
    }

    public function destroy($id)
    {
        $indemnite = $this->service->supprimerIndemniteAnnuelType($id);
        return response()->json(['data' => $indemnite]);
    }
}
