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
    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|required',
            'montant' => 'numeric|required',
            'compte_id' => 'integer|exists:comptes,id|required',
            'ecriture_categorie_id' => 'integer|exists:ecriture_categories,id|required',
            'type_unite_id' => 'integer|exists:type_unites,id|required',
            'type' => 'integer|required|min:1|max:2',
        ]);
        $type = $this->service->ajouterHeureExerciceType($data);

        return response()->json(['data' => $type]);
    }

    /**
     * Retourne un type
     * 
     * @param int $id id du type souhaité
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|required',
            'montant' => 'numeric|required',
            'compte_id' => 'integer|exists:comptes,id|required',
            'ecriture_categorie_id' => 'integer|exists:ecriture_categories,id|required',
            'type_unite_id' => 'integer|exists:type_unites,id|required',
            'type' => 'integer|required|min:1|max:2',
        ]);
        $type = $this->service->modifierHeureExerciceType($id, $data);

        return response()->json(['data' => $type]);
    }

    /**
     * Retourne un type
     * 
     * @param int $id id du type souhaité
     */
    public function destroy($id)
    {
        $this->service->supprimerHeureExerciceType($id);
        return response()->json(['data' => 'ok']);
    }
}
