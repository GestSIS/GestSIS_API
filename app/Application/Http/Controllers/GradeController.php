<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurParamService;
use App\Infrastructure\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
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
        $grades = $this->service->grades();

        return response()->json(['data' => $grades]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'abreviation' => 'string|min:1',
            'groupe' => 'integer|min:1',
            'tri' => 'integer'
        ]);

        $grade = $this->service->ajouterGrade($data);
        return response()->json(['data' => $grade]);
    }

    public function update(Request $request, $id)
    {
        if (!Grade::where('id', $id)->exists()) {
            return response()->json(['error' => 'Grade not found'], 404);
        }

        $data = $request->validate([
            'designation' => 'string|min:1',
            'abreviation' => 'string|min:1',
            'groupe' => 'integer|min:1',
            'tri' => 'integer'
        ]);

        $grade = $this->service->modifierGrade($id, $data);
        return response()->json(['data' => $grade]);
    }

    public function destroy($id)
    {
        if (!Grade::where('id', $id)->exists()) {
            return response()->json(['error' => 'Grade not found'], 404);
        }

        $grade = $this->service->supprimerGrade($id);
        return response()->json(['data' => $grade]);
    }
}
