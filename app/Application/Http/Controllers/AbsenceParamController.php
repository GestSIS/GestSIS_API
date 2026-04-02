<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\AbsenceParamBusiness;
use App\Infrastructure\Models\AbsenceParam;
use Illuminate\Http\Request;

class AbsenceParamController extends Controller
{
    public function index()
    {
        return response()->json(['data' => AbsenceParam::first()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'actif' => 'required|boolean',
        ]);

        return response()->json(['data' => AbsenceParamBusiness::updateParams($data)]);
    }
}
