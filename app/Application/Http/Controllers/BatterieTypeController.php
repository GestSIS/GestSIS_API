<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\BatteryBusiness;
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
        $batteries = BatteryBusiness::listBatteries();
        return response()->json(['data' => $batteries]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'string|min:1|required',
        ]);

        $batterie = BatteryBusiness::createBattery($data);
        return response()->json(['data' => $batterie]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'string|min:1|required',
        ]);

        $batterie = BatteryBusiness::editBattery($id, $data);
        return response()->json(['data' => $batterie]);
    }

    public function destroy($id)
    {
        $batterie = BatteryBusiness::deleteBattery($id);
        return response()->json(['data' => $batterie]);
    }
}
