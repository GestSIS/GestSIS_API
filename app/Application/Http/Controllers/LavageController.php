<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\LavageBusiness;
use Illuminate\Http\Request;

class LavageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $depuis = $request->input('depuis', null);
        if ($depuis) {
            $lavages = LavageBusiness::getLavagesDepuis($depuis);
        } else {
            $lavages = LavageBusiness::getAllLavages();
        }

        return response()->json(['data' => $lavages]);
    }

    /**
     * Créer un ou des lavages
     */
    public function create(Request $request)
    {
        $data = $request->validate([
            'lavages.*.date' => 'date|required',
            'lavages.*.article_id' => 'integer|required',
        ]);

        $materiels = LavageBusiness::createLavages($data['lavages']);
        return response()->json(['data' => $materiels]);
    }

    /**
     * Modifier des lavages
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'lavages.*.id' => 'integer|required',
            'lavages.*.date' => 'date|required',
        ]);

        $lavages = LavageBusiness::editLavages($data['lavages']);
        return response()->json(['data' => $lavages]);
    }

    /**
     * Supprimer des lavages
     */
    public function destroy(Request $request)
    {
        $data = $request->validate([
            'lavageIds' => 'required|array',
            'lavageIds.*' => 'required|integer',
        ]);

        $lavages = LavageBusiness::deleteLavages($data['lavageIds']);
        return response()->json(['data' => $lavages]);
    }
}
