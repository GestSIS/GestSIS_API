<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\BatterieBusiness;
use App\Models\BatterieType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BatterieTypeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $batteries = BatterieType::orderBy('nom')->get();
        return response()->json(['data' => $batteries]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'string|min:1|required|unique:batterie_types,nom',
        ]);

        $batterie = BatterieBusiness::createBatterie($data);
        return response()->json(['data' => $batterie], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => ['string', 'min:1', 'required', Rule::unique('batterie_types', 'nom')->ignore($id)],
        ]);

        $batterie = BatterieBusiness::editBatterie($id, $data);
        return response()->json(['data' => $batterie]);
    }

    public function destroy($id)
    {
        $batterie = BatterieBusiness::deleteBatterie($id);
        return response()->json(null, 204);
    }
}
