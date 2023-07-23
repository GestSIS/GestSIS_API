<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurParamService;
use Illuminate\Http\Request;

class FonctionController extends Controller
{
    protected $service;

    public function __construct(SapeurParamService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fonctions = $this->service->fonctions();

        return response()->json(['data' => $fonctions]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'string|min:1|required',
            'abreviation' => 'string|min:1|required',
            'cumulable' => 'boolean|required',
            'actif' => 'boolean|required',
            'tri' => 'integer|required'
        ]);

        $fonction = $this->service->ajouterFonction($data);
        return response()->json(['data' => $fonction]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'string|min:1|required',
            'abreviation' => 'string|min:1|required',
            'cumulable' => 'boolean|required',
            'actif' => 'boolean|required',
            'tri' => 'integer|required'
        ]);

        $fonction = $this->service->modifierFonction($id, $data);
        return response()->json(['data' => $fonction]);
    }

    public function destroy($id)
    {
        $fonction = $this->service->supprimerFonction($id);
        return response()->json(['data' => $fonction]);
    }
}
