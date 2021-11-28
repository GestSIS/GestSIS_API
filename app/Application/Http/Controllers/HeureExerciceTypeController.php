<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Domaine\API\PaiementService;

class HeureExerciceTypeController extends Controller
{

    public function __construct(PaiementService $service)
    {
        $this->service = $service;
    }

    /**
     * Créer un fichier iso20022 pour un décompte
     * 
     * @param int $id id du décompte pour lequelle le fichier doit être créé
     */
    public function index()
    {
        //return $this->service->impressionDecompte($id);
        //TODO:
    }

    /**
     * Retourne un décompte
     * 
     * @param int $id id du décompte souhaité
     */
    public function store($id)
    {
        $decompte = $this->service->getDecompteParId($id);

        return response()->json(['data' => $decompte]);
    }

    /**
     * Retourne un décompte
     * 
     * @param int $id id du décompte souhaité
     */
    public function update($id)
    {
        $decompte = $this->service->getDecompteParId($id);

        return response()->json(['data' => $decompte]);
    }

    /**
     * Retourne un décompte
     * 
     * @param int $id id du décompte souhaité
     */
    public function destroy($id)
    {
        $decompte = $this->service->getDecompteParId($id);

        return response()->json(['data' => $decompte]);
    }
}
