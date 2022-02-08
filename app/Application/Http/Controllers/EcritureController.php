<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ImputationService;
use Illuminate\Http\Request;

class EcritureController extends Controller
{
    protected $service;

    public function __construct(ImputationService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'total' => 'numeric|required',

            'solde' => 'numeric',
            'indemnite' => 'numeric',
            'frais' => 'numeric',

            'tarif' => 'numeric',
            'type_unite_id' => 'integer|nullable',
            'quantite' => 'numeric|nullable',

            'solde_min' => 'numeric|nullable',
            'solde_min_pour' => 'numeric|nullable',
            'taux' => 'numeric|nullable',
            'taux_description' => 'string|nullable',

            'date' => 'date|nullable',
            'heure' => 'time|nullable',

            'sapeur_id' => 'integer|required|exists:sapeurs,id',
            'exercice_comptable_id' => 'integer|required|exists:exercice_comptables,id',

            'intervention_id' => 'integer|nullable',
            'exercice_id' => 'integer|nullable',

            // Non modifiable
            // - 'decompte_id',

            'compte_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',

            'type' => 'integer|min:0|required',
        ]);

        $ecriture = $this->service->ajouterEcriture($data);
        return response()->json(['data' => $ecriture]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'amendable' => 'boolean|required',
            'duree_base' => 'integer|required',
            'statut' => 'integer|required',
            'tri' => 'integer|required'
        ]);

        $ecriture = $this->service->modifierEcriture($id, $data);
        return response()->json(['data' => $ecriture]);
    }

    public function destroy($id)
    {
        $ecriture = $this->service->supprimerEcriture($id);
        return response()->json(['data' => $ecriture]);
    }

    public function all(int $exerciceComptableId)
    {
        $ecritures = $this->service->getAllEcrituresForExerciceComptableById($exerciceComptableId);

        return response()->json(['data' => $ecritures]);
    }

    public function divers(int $exerciceComptableId)
    {
        $ecritures = $this->service->getEcrituresDiversForExerciceComptableById($exerciceComptableId);

        return response()->json(['data' => $ecritures]);
    }

    public function annuel(int $exerciceComptableId)
    {
        $ecritures = $this->service->getEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);

        return response()->json(['data' => $ecritures]);
    }

    public function amende(int $exerciceComptableId)
    {
        $ecritures = $this->service->getEcrituresAmendesForExerciceComptableById($exerciceComptableId);

        return response()->json(['data' => $ecritures]);
    }

    public function intervention(int $interventionId)
    {
        $ecritures = $this->service->getEcrituresForInterventionById($interventionId);

        return response()->json(['data' => $ecritures]);
    }

    public function exercice(int $exerciceId)
    {
        $ecritures = $this->service->getEcrituresForExerciceById($exerciceId);

        return response()->json(['data' => $ecritures]);
    }

    public function exercices(int $exerciceComptableId)
    {
        $ecritures = $this->service->getEcrituresForExercicesByExerciceComptable($exerciceComptableId);

        return response()->json(['data' => $ecritures]);
    }
}
