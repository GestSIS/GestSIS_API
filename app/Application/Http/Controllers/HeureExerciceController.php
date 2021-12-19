<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Domaine\API\PaiementService;

class HeureExerciceController extends Controller
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
    public function index($exerciceId)
    {
        return $this->service->heuresExercice();
    }

    /**
     * Retourne un décompte
     * 
     * @param int $id id du décompte souhaité
     */
    public function store(Request $request, $exerciceId)
    {
        //TODO:
        $data = [];
        $heure = $this->service->ajouterHeureExercice($exerciceId, $data);

        return response()->json(['data' => $heure]);
    }

    /**
     * Retourne un décompte
     * 
     * @param int $id id du décompte souhaité
     */
    public function update(Request $request, $exerciceId, $id)
    {
        //TODO:
        $data = [];
        $heure = $this->service->modifierHeureExercice($exerciceId, $id, $data);

        return response()->json(['data' => $heure]);
    }

    /**
     * Retourne un décompte
     * 
     * @param int $id id du décompte souhaité
     */
    public function destroy($exerciceId, $id)
    {
        $heure = $this->service->supprimerHeureExercice($exerciceId, $id);

        return response()->json(['data' => $heure]);
    }
}
