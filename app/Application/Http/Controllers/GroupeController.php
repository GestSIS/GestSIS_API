<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurParamService;
use Illuminate\Http\Request;

class GroupeController extends Controller
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
        $groupes = $this->service->groupes();

        return response()->json(['data' => $groupes]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'duree_validite' => 'integer|min:1',
            'expirable' => 'boolean',
            'tri' => 'integer',
            'actif' => 'boolean',
        ]);

        $groupe = $this->service->ajouterGroupe($data);
        return response()->json(['data' => $groupe]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'duree_validite' => 'integer|min:1',
            'expirable' => 'boolean',
            'tri' => 'integer',
            'actif' => 'boolean',
        ]);

        $groupe = $this->service->modifierGroupe($id, $data);
        return response()->json(['data' => $groupe]);
    }

    public function destroy($id)
    {
        $groupe = $this->service->supprimerGroupe($id);
        return response()->json(['data' => $groupe]);
    }
}
