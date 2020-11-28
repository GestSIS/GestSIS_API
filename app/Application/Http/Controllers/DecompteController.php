<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Domaine\API\ComptabiliteService;
use App\Domaine\Business\PaiementBusiness;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Paiement;
use Z38\SwissPayment\BIC;
use Z38\SwissPayment\IBAN;
use Z38\SwissPayment\IID;
use Z38\SwissPayment\Message\CustomerCreditTransfer;
use Z38\SwissPayment\PaymentInformation\PaymentInformation;
use Z38\SwissPayment\StructuredPostalAddress;
use Z38\SwissPayment\TransactionInformation\BankCreditTransfer;
use Z38\SwissPayment\Money;

class DecompteController extends Controller
{

    public function __construct(ComptabiliteService $service)
    {
        $this->service = $service;
    }

    /**
     * creer un décompte
     * $designation - nom du décompte
     * $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     * $taux_avs - taux avs payé par le sapeur
     * $taux_ac - taux ac payé par le sapeur
     * $deduction - true si les déduction doivent être faites sur ce paiement
     * $minimumImposableAVSAC - montant imposable minimum pour l'avs
     * $minimumSoldeImposable - montant minimum pour que la solde soit imposable
     */
    public function creer(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string',
            'taux_avs' => 'numeric|min:0|max:1|nullable',
            'taux_ac' => 'numeric|min:0|max:1|nullable',
            'deduction' => 'boolean',
            'exerciceComptableId' => 'integer|min:1',
            'minimumImposableAVSAC' => 'numeric',
            'minimumSoldeImposable' => 'numeric'
        ]);

        return response()->json(['data' => PaiementBusiness::creerDecompte(
            $data['designation'],
            $data['exerciceComptableId'],
            $data['deduction'],
            $data['taux_ac'],
            $data['taux_avs'],
            $data['minimumSoldeImposable'],
            $data['minimumImposableAVSAC']
        )]);
    }

    public function iso20022(Request $request)
    {
        $data = $request->validate([
            'decompteId' => 'integer|min:1',
            'nom' => 'string',
            'iban' => 'string',
            'bic' => 'string',
        ]);

        return response()->streamDownload(function () use ($data) {
            echo PaiementBusiness::iso20022FromDecompte($data['decompteId'], $data['nom'], $data['bic'], $data['iban']);
        }, Decompte::find($data['decompteId'])->designation.".xml");
    }

    /**
     * Retourne tous les décomptes
     */
    public function getAll()
    {
        $decomptes = Decompte::all();

        return response()->json(['data' => $decomptes]);
    }

    /**
     * Retourne un décompte
     * $id - id du décompte souhaité
     */
    public function get($id)
    {
        $decomptes = Decompte::find($id);

        return response()->json(['data' => $decomptes]);
    }

    /**
     * Retourne tous les décompte pour un exercice comptable
     * $id - id de l'exercice comptable
     */
    public function getByExerciceComptable($id)
    {
        $decomptes = Decompte::where('exercice_comptable_id', $id)->get();

        return response()->json(['data' => $decomptes]);
    }
}
