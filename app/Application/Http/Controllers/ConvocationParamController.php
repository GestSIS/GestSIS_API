<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ConvocationParamService;
use Illuminate\Http\Request;

class ConvocationParamController extends Controller
{
    protected $service;

    public function __construct(ConvocationParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $params = $this->service->params();

        return response()->json(['data' => $params]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre' => 'required|string|nullable',
            'texte_debut' => 'string|nullable',
            'texte_fin' => 'string|nullable',
            'texte_pour_info' => 'string|nullable',
            'affichage_duree' => 'required|bool',
            'affichage_pour_info' => 'required|bool',
        ]);

        $params = $this->service->updateParams($data);

        return response()->json(['data' => $params]);
    }
}
