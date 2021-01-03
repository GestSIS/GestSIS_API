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
     * 
     * @param string $designation - nom du décompte
     * @param int $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     * @param float $taux_avs - taux avs payé par le sapeur
     * @param float $taux_ac - taux ac payé par le sapeur
     * @param boolean $deduction - true si les déduction doivent être faites sur ce paiement
     * @param float $minimumImposableAVSAC - montant imposable minimum pour l'avs
     * @param float $minimumSoldeImposable - montant minimum pour que la solde soit imposable
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

    /**
     * Créer un fichier iso20022 pour un décompte
     * 
     * @param int $id id du décompte pour lequelle le fichier doit être créé
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
            echo PaiementBusiness::iso20022FromDecompte($id, $data['nom'], $data['bic'], $data['iban']);
        }, Decompte::find($id)->designation.".xml");
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

    public function CertificatSalaireSapeur()
    {
        PaiementBusiness::certificatSalaire(2, 3);
    }
}
