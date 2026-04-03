<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ImputationBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Ecriture;
use App\Models\Exercice;
use Illuminate\Http\Request;

class EcritureController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validateEcriture($request);
        $ecriture = ImputationBusiness::ajouterEcriture($data);
        return response()->json(['data' => $ecriture]);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validateEcriture($request);
        $ecriture = ImputationBusiness::modifierEcriture($id, $data);
        return response()->json(['data' => $ecriture]);
    }

    private function validateEcriture($request)
    {
        $data = $request->validate([
            'module' => 'required|numeric|min:0',
        ]);

        $type = $request->get('module');
        $data = null;

        switch ($type) {
            case 0:
                $data = $request->validate([
                    'designation' => 'string|min:1|required',
                    'complement' => 'string|nullable',
                    'total' => 'numeric|required',

                    'solde' => 'numeric',
                    'indemnite' => 'numeric',
                    'frais' => 'numeric',

                    'tarif' => 'numeric',
                    'type_unite_id' => 'integer|required',
                    'quantite' => 'numeric|required',

                    'date' => 'date|required',

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
        $ecriture = ImputationBusiness::supprimerEcriture($id);
        return response()->json(['data' => $ecriture]);
    }

    public function all(int $exerciceComptableId)
    {
        return response()->json(['data' => Ecriture::where('exercice_comptable_id', $exerciceComptableId)->get()]);
    }

    public function divers(int $exerciceComptableId)
    {
        return response()->json([
            'data' => Ecriture::where('exercice_comptable_id', $exerciceComptableId)
                ->where('module', ImputationBusiness::ECRITURE_MODULE_DIVERS)->get()
        ]);
    }

    public function annuel(int $exerciceComptableId)
    {
        return response()->json([
            'data' => Ecriture::where('exercice_comptable_id', $exerciceComptableId)
                ->where('module', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL)->get()
        ]);
    }

    public function amende(int $exerciceComptableId)
    {
        return response()->json([
            'data' => Ecriture::where('exercice_comptable_id', $exerciceComptableId)
                ->where('module', ImputationBusiness::ECRITURE_MODULE_AMENDE)->get()
        ]);
    }

    public function intervention(int $interventionId)
    {
        return response()->json(['data' => Ecriture::where('intervention_id', $interventionId)->get()]);
    }

    public function exercice(int $exerciceId)
    {
        return response()->json(['data' => Ecriture::where('exercice_id', $exerciceId)->get()]);
    }

    public function exercices(int $exerciceComptableId)
    {
        return response()->json([
            'data' => Exercice::where([
                ['exercice_comptable_id', '=', $exerciceComptableId],
                ['statut', '>', 2],
            ])->with('ecritures')->get()
        ]);
    }
}
