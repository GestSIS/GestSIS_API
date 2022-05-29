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
            'abreviation' => 'string|min:1|required',
            'designation' => 'string|min:1|required',
            'validite_debut' => 'date|nullable',
            'validite_fin' => 'date|nullable',
            'fonction_id' => 'integer|nullable',
            'grade_id' => 'integer|nullable',
            'precedent_id' => 'integer|nullable',
            'duree' => 'numeric|required|min:0',
            'tri' => 'integer|required',
        ]);

        $cours = $this->service->ajouterCours($data);
        return response()->json(['data' => $cours]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'abreviation' => 'string|min:1',
            'designation' => 'string|min:1',
            'validite_debut' => 'date|nullable',
            'validite_fin' => 'date|nullable',
            'fonction_id' => 'integer|nullable',
            'grade_id' => 'integer|nullable',
            'precedent_id' => 'integer|nullable',
            'duree' => 'numeric|min:0|nullable',
            'tri' => 'integer',
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
