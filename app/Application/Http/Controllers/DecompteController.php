<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\PaiementBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Collections\AFacturerExport;
use App\Collections\EcrituresExport;
use App\Models\Decompte;
use App\Models\Ecriture;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DecompteController extends Controller
{

    /**
     * Créer un décompte annuel
     */
    public function creerAnnuel(Request $request)
    {
        $data = $request->validate([
            'exercice_comptable_id' => 'required|integer|min:1',
            'date' => 'required|date',
            'designation' => 'required|string|min:1',
            'sapeurIds' => 'nullable|array',
            'sapeurIds.*' => 'integer|min:1',
        ]);

        $selection = $request->validate([
            'ecrituresAmende' => 'required|boolean',
            'ecrituresAnnuel' => 'required|boolean',
            'ecrituresCours' => 'required|boolean',
            'ecrituresDivers' => 'required|boolean',
            'ecrituresTravail' => 'required|boolean',
            'ecrituresExercice' => 'required|boolean',
            'ecrituresIntervention' => 'required|boolean',
        ]);

        try {
            $decompte = PaiementBusiness::creerDecompteAnnuel(
                $data['exercice_comptable_id'],
                $data['date'],
                $data['designation'],
                $selection,
                $data['sapeurIds'] ?? []
            );
        } catch (ArrayException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }
        return response()->json(['data' => $decompte]);
    }

    /**
     * Créer un décompte pour un sapeur
     */
    public function creerSapeur(Request $request)
    {
        $data = $request->validate([
            'exercice_comptable_id' => 'required|integer|min:1',
            'sapeur_id' => 'required|integer|min:1',
            'date' => 'required|date',
        ]);

        $decompte = PaiementBusiness::creerDecompteSapeur($data['exercice_comptable_id'], $data['sapeur_id'], $data['date']);
        return response()->json(['data' => $decompte]);
    }

    /**
     * Créer un décompte pour un exercice
     */
    public function creerExercice(Request $request)
    {
        $data = $request->validate([
            'exercice_id' => 'required|integer|min:1',
            'date' => 'required|date',
            'deduction' => 'required|boolean',
        ]);

        $decompte = PaiementBusiness::creerDecompteExercice($data['exercice_id'], $data['date'], $data['deduction']);
        return response()->json(['data' => $decompte]);
    }

    public function ecritures(int $decompteId)
    {
        return response()->json(['data' => Ecriture::where('decompte_id', '=', $decompteId)->get()]);
    }

    /**
     * Supprimer un décompte
     */
    public function destroy($decompteId)
    {
        $res = PaiementBusiness::supprimerDecompte($decompteId);
        return response()->json(['data' => $res]);
    }

    /**
     * Créer un fichier iso20022 pour un décompte
     */
    public function iso20022($id)
    {
        return PaiementBusiness::iso20022PourDecompteStream($id);
    }

    public function exportEcritures($id)
    {
        return Excel::download(new EcrituresExport($id), 'ecritures.xlsx');
    }

    /**
     * Créer un fichier pdf pour un décompte
     */
    public function print(Request $request, $id)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return PaiementBusiness::impressionDecompte($id, $sisKey);
    }

    /**
     * Retourne un décompte pour tous les sapeurs
     */
    public function printParSapeur(Request $request, $decompteId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return PaiementBusiness::impressionDecompteParSapeur($decompteId, $sisKey);
    }

    /**
     * Retourne un résumé comptable pour tous les sapeurs
     */
    public function resumeParSapeur(Request $request, int $exerciceComptableId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return PaiementBusiness::impressionResumeParSapeur($exerciceComptableId, $sisKey);
    }

    /**
     * Retourne un résumé comptable pour 1 sapeur
     */
    public function resumePourSapeur(Request $request, int $exerciceComptableId, int $sapeurId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return PaiementBusiness::impressionResumePourSapeur($exerciceComptableId, $sapeurId, $sisKey);
    }

    /**
     * Retourne un décompte pour 1 sapeur
     */
    public function printPourSapeur(Request $request, $decompteId, $sapeurId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return PaiementBusiness::impressionDecompteSapeur($decompteId, $sapeurId, $sisKey);
    }

    public function printParCompte($exerciceComptableId)
    {
        throw new ArrayException([], "Non implémenté pour le moment");
    }

    public function aFacturer($decompteId)
    {
        return Excel::download(new AFacturerExport($decompteId), 'a_facturer.xlsx');
    }

    /**
     * Retourne un décompte
     */
    public function show($id)
    {
        return response()->json(['data' => Decompte::whereId($id)->with('paiements')->first()]);
    }

    /**
     * Retourne tous les décomptes pour un exercice comptable
     */
    public function getByExerciceComptable($id)
    {
        return response()->json(['data' => Decompte::where('exercice_comptable_id', $id)->get()]);
    }

    /**
     * Retourne le certificat d'un sapeur pour un exercice comptable
     */
    public function certificatSalaireSapeur($exerciceComptableId, $sapeurId)
    {
        return PaiementBusiness::certificatSalaireSapeur($exerciceComptableId, $sapeurId, true);
    }

    /**
     * Retourne le certificat de tous les sapeurs pour un exercice comptable
     */
    public function certificatSalaire($exerciceComptableId)
    {
        return PaiementBusiness::certificatSalaire($exerciceComptableId, true);
    }
}
