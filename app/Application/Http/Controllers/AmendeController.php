<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use Illuminate\Http\Request;

class AmendeController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteParamService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $amendes = $this->service->amendes();

        return response()->json(['data' => $amendes]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'compte_id' => 'required|integer',
            'ecriture_categorie_id' => 'required|integer',
            'amendes.*.montant' => 'required|numeric',
        ]);
        $amendes = $this->service->updateAmendes($data);

        return response()->json(['data' => $amendes]);
    }
}
