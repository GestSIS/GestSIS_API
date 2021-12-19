<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Domaine\API\ExerciceParamService;

class HeureExerciceTypeController extends Controller
{

    public function __construct(ExerciceParamService $service)
    {
        $this->service = $service;
    }

    /**
     * Créer un fichier iso20022 pour un type
     * 
     * @param int $id id du type pour lequelle le fichier doit être créé
     */
    public function index()
    {
        return $this->service->heuresExerciceType();
    }

    /**
     * Retourne un type
     * 
     * @param int $id id du type souhaité
     */
    public function store(Request $request, $exerciceId)
    {
        //TODO:
        $data = [];
        $type = $this->service->ajouterHeureExerciceType($exerciceId, $data);

        return response()->json(['data' => $type]);
    }

    /**
     * Retourne un type
     * 
     * @param int $id id du type souhaité
     */
    public function update(Request $request, $exerciceId, $id)
    {
        //TODO:
        $data = [];
        $type = $this->service->ajouterHeureExerciceType($exerciceId, $id, $data);

        return response()->json(['data' => $type]);
    }

    /**
     * Retourne un type
     * 
     * @param int $id id du type souhaité
     */
    public function destroy($id)
    {
        //TODO:
        $data = [];
        $type = $this->service->ajouterHeureExerciceType($data);

        return response()->json(['data' => $type]);
    }
}
