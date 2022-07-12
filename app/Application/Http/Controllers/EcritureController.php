<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ImputationService;
use App\Domaine\Exceptions\ArrayException;
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
        $data = $this->validateEcriture($request);

        $ecriture = $this->service->ajouterEcriture($data);
        return response()->json(['data' => $ecriture]);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validateEcriture($request);

        $ecriture = $this->service->modifierEcriture($id, $data);
        return response()->json(['data' => $ecriture]);
    }

    private function validateEcriture($request)
    {

        $data = $request->validate([
            'module' => 'required|numeric|min:0',
        ]);
        // Types effectifs:
        // 0. Divers
        // 1. Exercice
        // 2. Intervention
        // 3. Frais Annuel
        // 4. Indemnité Annuel
        // 5. AVS
        // 6. Amende
        // 7. Décompte d'heures
        // 8. Cours
        // 9. Remboursement à l'employeur ?

        $type = $request->get('module');
        $data = null;

        switch ($type) {
            case 0:
                $data = $request->validate([
                    'designation' => 'string|min:1|required',
                    'total' => 'numeric|required',

                    'solde' => 'numeric',
                    'indemnite' => 'numeric',
                    'frais' => 'numeric',

                    'tarif' => 'numeric',
                    'type_unite_id' => 'integer|required',
                    'quantite' => 'numeric|required',

                    'date' => 'date|nullable',

                    'sapeur_id' => 'integer|required|exists:sapeurs,id',
                    'exercice_comptable_id' => 'integer|required|exists:exercice_comptables,id',

                    'compte_id' => 'integer|required',
                    'ecriture_categorie_id' => 'integer|required',

                    'type' => 'integer|min:0|required',
                    'module' => 'integer|min:0|required',
                ]);
                break;

            default:
                throw new ArrayException([], 'Type d\'écriture non-supporté');
        }
        return $data;
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
