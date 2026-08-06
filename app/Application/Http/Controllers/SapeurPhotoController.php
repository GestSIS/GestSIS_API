<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurPhotoController extends Controller
{
    public function index(Request $request, int $id)
    {
        $sisKey = $request->header('Sis-Key', Null);
        return SapeurBusiness::downloadPhotoSapeur($id, $sisKey);
    }

    public function store(Request $request, $sapeurId)
    {
        if ($request->hasFile('image')) {
            if ($request->file('image')->isValid()) {
                $validated = $request->validate([
                    'image' => 'required|mimes:jpg,jpeg,png|max:1014',
                ]);
                if (is_null($sapeurId) || !Sapeur::find($sapeurId)) {
                    return response()->json(['data' => ['message' => 'Sapeur inexistant']], 500);
                }

                $sisKey = $request->header('Sis-Key', Null);
                $res = SapeurBusiness::uploadPhotoSapeur($validated['image'], $sapeurId, $sisKey);
                return response()->json(['data' => $res]);
            }
            return response()->json(['data' => ['message' => 'Image invalide']], 500);
        }
        return response()->json(['data' => ['message' => 'Image manquante']], 500);
    }

    public function destroy(Request $request, int $id)
    {
        $sisKey = $request->header('Sis-Key', Null);
        SapeurBusiness::deletePhotoSapeur($id, $sisKey);
        return response()->json(['data' => "success"]);
    }
}
