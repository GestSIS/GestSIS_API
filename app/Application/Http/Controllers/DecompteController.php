<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Domaine\API\PaiementService;
use App\Domaine\Business\PaiementBusiness;
use App\Infrastructure\Models\Decompte;

class DecompteController extends Controller
{

    public function __construct(PaiementService $service)
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
     * @param float $franchiseAvs - montant imposable minimum pour l'avs
     * @param float $franchiseImposition - montant minimum pour que la solde soit imposable
     */
    public function creerAnnuel(Request $request)
    {
        $data = $request->validate([
            'exercice_comptable_id' => 'required|integer|min:1',
            'date' => 'required|date',
            'designation' => 'required|string|min:1',
        ]);

        $decompte = $this->service->creerDecompteAnnuel($data['exercice_comptable_id'], $data['date'], $data['designation']);
        return response()->json(['data' => $decompte]);
    }

    public function creerSapeur(Request $request)
    {
        $data = $request->validate([
            'exercice_comptable_id' => 'required|integer|min:1',
            'sapeur_id' => 'required|integer|min:1',
            'date' => 'required|date',
        ]);

        $decompte = $this->service->creerDecompteSapeur($data['exercice_comptable_id'], $data['sapeur_id'], $data['date']);
        return response()->json(['data' => $decompte]);
    }

    public function creerExercice(Request $request)
    {
        $data = $request->validate([
            'exercice_id' => 'required|integer|min:1',
            'date' => 'required|date',
            'deduction' => 'required|boolean',
        ]);

        $decompte = $this->service->creerDecompteExercice($data['exercice_id'], $data['date'], $data['deduction']);
        return response()->json(['data' => $decompte]);
    }
    /**
     * Créer un fichier iso20022 pour un décompte
     * 
     * @param int $id id du décompte pour lequelle le fichier doit être créé
     * @param string $nom titulaire du compte débiteur
     * @param string $bic bic de la banque du compte débiteur
     * @param string $iban iban du compte débiteur
     */
    public function iso20022($id)
    {
        return $this->service->iso20022PourDecompte($id);
    }

    /**
     * Retourne un décompte
     * 
     * @param int $id id du décompte souhaité
     */
    public function get($id)
    {
        $decompte = $this->service->getDecompteParId($id);

        return response()->json(['data' => $decompte]);
    }

    /**
     * Retourne tous les décompte pour un exercice comptable
     * 
     * @param int $id id de l'exercice comptable
     */
    public function getByExerciceComptable($id)
    {
        $decomptes = $this->service->getDecomptePourExerciceComptable($id);

        return response()->json(['data' => $decomptes]);
    }

    /**
     * Retoune le certificat d'un sapeur pour un exercice comptable
     * 
     * @param int $exerciceComptableId id de l'exercice comp
     * @param int $sapeurId id du sapeur
     */
    public function certificatSalaireSapeur($exerciceComptableId, $sapeurId)
    {
        return $this->service->certificatSalaireSapeur($exerciceComptableId, $sapeurId);
    }

    /**
     * Retoune le certificat de tous les sapeurs pour un exercice comptable
     * 
     * @param int $exerciceComptableId id de l'exercice comp
     */
    public function certificatSalaire($exerciceComptableId)
    {
        return $this->service->certificatSalairePourExerciceComptable($exerciceComptableId);
    }
}
