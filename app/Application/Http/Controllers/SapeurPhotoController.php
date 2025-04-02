<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurPhotoController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function index(Request $request, int $id)
    {
        $sisKey = $request->header('Sis-Id', Null);
        return $this->service->downloadPhotoSapeur($id, $sisKey);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request, $sapeurId)
    {
        if ($request->hasFile('image')) {
            //  Let's do everything here
            if ($request->file('image')->isValid()) {
                $validated = $request->validate([
                    'image' => 'required|mimes:jpg,jpeg,png|max:1014',
                ]);
                if (is_null($sapeurId) || !Sapeur::find($sapeurId)) {
                    return response()->json(['data' => ['message' => 'Sapeur inexistant']], 500);
                }

                $sisKey = $request->header('Sis-Id', Null);
                $res = $this->service->uploadPhotoSapeur($validated['image'], $sapeurId, $sisKey);
                return response()->json(['data' => $res]);
            }
            return response()->json(['data' => ['message' => 'Image invalide']], 500);
        }
        return response()->json(['data' => ['message' => 'Image manquante']], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request, int $id)
    {
        $sisKey = $request->header('Sis-Id', Null);
        $this->service->deletePhotoSapeur($id, $sisKey);

        return response()->json(['data' => "success"]);
    }
}
