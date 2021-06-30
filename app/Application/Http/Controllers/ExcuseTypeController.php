<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ExerciceParamService;
use Illuminate\Http\Request;

class ExcuseTypeController extends Controller
{
    protected $service;

    public function __construct(ExerciceParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $excusesTypes = $this->service->excusesTypes();

        return response()->json(['data' => $excusesTypes]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'abreviation' => 'string|min:1',
            'amende' => 'integer',
            'status' => 'integer',
            'tri' => 'integer'
        ]);

        $excuseType = $this->service->ajouterExcuseType($data);
        return response()->json(['data' => $excuseType]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'abreviation' => 'string|min:1',
            'amende' => 'integer',
            'status' => 'integer',
            'tri' => 'integer'
        ]);

        $excuseType = $this->service->modifierExcuseType($id, $data);
        return response()->json(['data' => $excuseType]);
    }

    public function destroy($id)
    {
        $excuseType = $this->service->supprimerExcuseType($id);
        return response()->json(['data' => $excuseType]);
    }
}
