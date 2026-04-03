<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurPhotoController extends Controller
{
    protected $business;

    public function __construct(SapeurBusiness $business)
    {
        $this->business = $business;
    }

    public function index(Request $request, int $id)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return $this->business->downloadPhotoSapeur($id, $sisKey);
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

                $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
                $res = $this->business->uploadPhotoSapeur($validated['image'], $sapeurId, $sisKey);
                return response()->json(['data' => $res]);
            }
            return response()->json(['data' => ['message' => 'Image invalide']], 500);
        }
        return response()->json(['data' => ['message' => 'Image manquante']], 500);
    }

    public function destroy(Request $request, int $id)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        $this->business->deletePhotoSapeur($id, $sisKey);
        return response()->json(['data' => "success"]);
    }
}
