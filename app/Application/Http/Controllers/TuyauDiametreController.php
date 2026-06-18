<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\DiameterBusiness;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TuyauDiametreController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $diametres = DiameterBusiness::listDiameters();
        return response()->json(['data' => $diametres]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'diametre' => 'integer|min:1|required|unique:tuyau_diametres,diametre',
        ]);

        $diametre = DiameterBusiness::createDiameter($data);
        return response()->json(['data' => $diametre]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'diametre' => [
                'integer', 'min:1', 'required',
                Rule::unique('tuyau_diametres', 'diametre')->ignore($id),
            ],
        ]);

        $diametre = DiameterBusiness::editDiameter($id, $data);
        return response()->json(['data' => $diametre]);
    }

    public function destroy($id)
    {
        $diametre = DiameterBusiness::deleteDiameter($id);
        return response()->json(['data' => $diametre]);
    }
}
