<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurParamService;
use Illuminate\Http\Request;

class CoursController extends Controller
{
    protected $service;

    public function __construct(SapeurParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $cours = $this->service->cours();

        return response()->json(['data' => $cours]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'duree_validite' => 'integer|min:1',
            'expirable' => 'boolean',
            'tri' => 'integer'
        ]);

        $cours = $this->service->ajouterCours($data);
        return response()->json(['data' => $cours]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'duree_validite' => 'integer|min:1',
            'expirable' => 'boolean',
            'tri' => 'integer'
        ]);

        $cours = $this->service->modifierCours($id, $data);
        return response()->json(['data' => $cours]);
    }

    public function destroy($id)
    {
        $cours = $this->service->supprimerCours($id);
        return response()->json(['data' => $cours]);
    }
}
