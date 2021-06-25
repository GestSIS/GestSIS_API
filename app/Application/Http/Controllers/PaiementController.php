<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\PaiementService;

class PaiementController extends Controller
{
    protected $service;

    public function __construct(PaiementService $service)
    {
        $this->service = $service;
    }

    /**
     * Créer un fichier iso20022 pour un paiement
     * 
     * @param int $id du paiement pour lequelle le fichier doit être créé
     */
    public function iso20022($id)
    {
        return $this->service->iso20022PourPaiementSapeur($id);
    }

    /**
     * Retourne un paiement
     * $id - id du paiement souhaité
     */
    public function get($id)
    {
        $paiement = $this->service->getPaiementSapeurParId($id);
        return response()->json(['data' => $paiement]);
    }

    public function getByExerciceComptable($exerciceComptableId)
    {
        $paiements = $this->service->getPaiementsPourExerciceComptable($exerciceComptableId);
        return response()->json(['data' => $paiements]);
    }
}
