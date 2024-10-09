<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\GroupeService;
use Illuminate\Http\Request;

class GroupeController extends Controller
{
    protected $service;

    public function __construct(GroupeService $service)
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
        $groupes = $this->service->listeGroupe();
        return response()->json(['data' => $groupes]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'no' => 'integer|nullable',
            'parent_id' => 'integer|nullable',
            'tri' => 'integer',
            'type' => 'integer',
        ]);

        $groupe = $this->service->ajouterGroupe($data);
        return response()->json(['data' => $groupe]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'no' => 'integer|nullable',
            'parent_id' => 'integer|nullable',
            'tri' => 'integer',
            'type' => 'integer',
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
