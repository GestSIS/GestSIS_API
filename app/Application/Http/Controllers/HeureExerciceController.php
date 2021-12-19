<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Domaine\API\ExerciceService;

class HeureExerciceController extends Controller
{

    public function __construct(ExerciceService $service)
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
        return $this->service->heuresExercice($exerciceId);
    }

    /**
     * Retourne un décompte
     * 
     * @param int $id id du décompte souhaité
     */
    public function store(Request $request, $exerciceId)
    {
        $data = $request->validate([
            // 'designation' => 'string',
            // 'quantite' => 'numeric',
            // 'compte_id' => 'integer|exists:comptes,id',
            // 'ecriture_categorie_id' => 'integer|exists:ecriture_categories,id',
            // 'type_unite_id' => 'integer|exists:type_unites,id',
            // Addition compare to type
            'montant' => 'numeric',
            'sapeur_id' => 'integer|exists:sapeurs,id',
            'heure_exercice_type_id' => 'integer|exists:heure_exercice_types,id',
        ]);
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
        $data = $request->validate([
            // 'designation' => 'string',
            // 'quantite' => 'numeric',
            // 'compte_id' => 'integer|exists:comptes,id',
            // 'ecriture_categorie_id' => 'integer|exists:ecriture_categories,id',
            // 'type_unite_id' => 'integer|exists:type_unites,id',
            // Addition compare to type
            'montant' => 'numeric',
        ]);
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
