<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SisParamService;
use App\Infrastructure\Models\LocaliteSis;
use Illuminate\Http\Request;

class LocaliteSisController extends Controller
{
    protected $service;

    public function __construct(SisParamService $service)
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
        $localites = LocaliteSis::pluck('localite_id')->toArray();

        return response()->json(['data' => $localites]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            '*' => 'integer|exists:localites,id|unique:localite_sis,localite_id',
        ]);

        $localites = $this->service->ajouterLocalitesSis($data);
        return response()->json(['data' => $localites]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            '*' => 'integer|exists:localite_sis,localite_id',
        ]);

        $localites = $this->service->supprimerLocalitesSis($data);
        return response()->json(['data' => $localites]);
    }
}
