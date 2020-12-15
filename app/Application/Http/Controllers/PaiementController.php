<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\PaiementBusiness;
use Illuminate\Http\Request;

use App\Infrastructure\Models\Paiement;
use App\Infrastructure\Models\Decompte;

class PaiementController extends Controller
{
    /**
     * Retourne tous les paiement
     */
    public function getAll(){
        $paiements = Paiement::all();

        return response()->json(['data' => $paiements]);
    }

     /**
     * Créer un fichier iso20022 pour un paiement
     * 
     * @param int $id id du paiement pour lequelle le fichier doit être créé
     * @param string $nom titulaire du compte débiteur
     * @param string $bic bic de la banque du compte débiteur
     * @param string $iban iban du compte débiteur
     */
    public function iso20022(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'string|required',
            'iban' => 'string|required',
            'bic' => 'string|required',
        ]);

        return response()->streamDownload(function () use ($data, $id) {
            echo PaiementBusiness::iso20022FromPaiement($id, $data['nom'], $data['bic'], $data['iban']);
        }, "paiement.xml");
    }

    /**
     * Retourne un paiement
     * $id - id du paiement souhaité
     */
    public function get($id){
        $paiements = Paiement::find($id);

        return response()->json(['data' => $paiements]);
    }

    /**
     * Retourne tous les paiements pour un décompte
     * $id - id du paiement
     */
    public function getByDecompte($id){
        $paiements = Paiement::where('decompte_id', $id)->get();

        return response()->json(['data' => $paiements]);
    }

    /**
     * Retourne tous les paiements pour un exercice comptable
     * $id - id de l'exercice comptable
     */
    public function getByExerciceComptable($id){
        $decomptes = Decompte::where('exercice_comptable_id', $id)->with('paiements')->get();

        return response()->json(['data' => $decomptes]);
    }
}
