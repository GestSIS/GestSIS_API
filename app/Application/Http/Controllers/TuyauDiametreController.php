<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\DiametreBusiness;
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
        $diametres = DiametreBusiness::listeDiametres();
        return response()->json(['data' => $diametres]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'diametre' => 'integer|min:1|required|unique:tuyau_diametres,diametre',
        ]);

        $diametre = DiametreBusiness::createDiametre($data);
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

        $diametre = DiametreBusiness::editDiametre($id, $data);
        return response()->json(['data' => $diametre]);
    }

    public function destroy($id)
    {
        $diametre = DiametreBusiness::deleteDiametre($id);
        return response()->json(['data' => $diametre]);
    }
}
