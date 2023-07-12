<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\AbsenceService;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    protected $service;

    public function __construct(AbsenceService $service)
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
        $absences = $this->service->listeAbsence();
        return response()->json(['data' => $absences]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sapeur_id' => 'integer|min:1',
            'debut' => 'date',
            'fin' => 'date',
        ]);

        $absence = $this->service->ajouterAbsence($data);
        return response()->json(['data' => $absence]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'sapeur_id' => 'integer|min:1',
            'debut' => 'date',
            'fin' => 'date',
        ]);

        $absence = $this->service->modifierAbsence($id, $data);
        return response()->json(['data' => $absence]);
    }

    public function destroy($id)
    {
        $absence = $this->service->supprimerAbsence($id);
        return response()->json(['data' => $absence]);
    }
}
