<?php

namespace App\Application\Http\Controllers;

use App\Infrastructure\Models\Groupe;
use Illuminate\Http\Request;

class GroupeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groupes = Groupe::all();

        return response()->json(['data' => $groupes]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pere_id' => 'integer|nullable',
            'type' => 'boolean',
            'no' => 'integer|nullable',
            'designation' => 'string',
            'info' => 'string|nullable',
            'tri' => 'integer',
        ]);

        $intervention = $this->service->createIntervention($data);

        return response()->json(['data' => $intervention]);
    }

    public function update(Request $request, $groupeId)
    {
        
    }

    public function delete(Request $request, $groupeId)
    {
        
    }
}
