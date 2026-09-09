<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\BatterieBusiness;
use Illuminate\Http\Request;

class BatterieTypeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $batteries = BatterieBusiness::listeBatteries();
        return response()->json(['data' => $batteries]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'string|min:1|required',
        ]);

        $batterie = BatterieBusiness::createBatterie($data);
        return response()->json(['data' => $batterie]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'string|min:1|required',
        ]);

        $batterie = BatterieBusiness::editBatterie($id, $data);
        return response()->json(['data' => $batterie]);
    }

    public function destroy($id)
    {
        $batterie = BatterieBusiness::deleteBatterie($id);
        return response()->json(['data' => $batterie]);
    }
}
