<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ComptabiliteParamBusiness;
use App\Models\Amende;
use Illuminate\Http\Request;

class AmendeController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Amende::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'compte_id' => 'required|integer',
            'ecriture_categorie_id' => 'required|integer',
            'amendes.*.montant' => 'required|numeric',
        ]);
        $amendes = ComptabiliteParamBusiness::updateAmendes($data);

        return response()->json(['data' => $amendes]);
    }
}
