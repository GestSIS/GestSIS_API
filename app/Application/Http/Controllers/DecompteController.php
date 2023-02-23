<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Domaine\API\PaiementService;
use App\Domaine\Exceptions\ArrayException;
use Exception;

class DecompteController extends Controller
{
    private $service = null;

    public function __construct(PaiementService $service)
    {
        $this->service = $service;
    }

    /**
     * Créer un décompte annuel
     * 
     * @param string $designation - nom du décompte
     * @param int $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     * @param date $date - date de la création du décompte
     */
    public function creerAnnuel(Request $request)
    {
        $data = $request->validate([
            'exercice_comptable_id' => 'required|integer|min:1',
            'date' => 'required|date',
            'designation' => 'required|string|min:1',
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
            $decompte = $this->service->creerDecompteAnnuel($data['exercice_comptable_id'], $data['date'], $data['designation'], $selection);
        } catch (ArrayException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }
        return response()->json(['data' => $decompte]);
    }

    /**
     * Créer un décompte
     * 
     * @param int $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     * @param date $date - date de la création du décompte
     * @param boolean $deduction - true si les déduction doivent être faites sur ce paiement
     */
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

    /**
     * Créer un décompte
     * 
     * @param int $exerciceId - id de l'exercice
     * @param date $date - date de la création du décompte
     * @param boolean $deduction - true si les déduction doivent être faites sur ce paiement
     */
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

    public function ecritures(Int $decompteId)
    {
        $ecritures = $this->service->getEcrituresPourDecompte($decompteId);
        return response()->json(['data' => $ecritures]);
    }

    /**
     * Supprimer un décompte
     * 
     * @param int $exerciceId - id de l'exercice
     * @param date $date - date de la création du décompte
     * @param boolean $deduction - true si les déduction doivent être faites sur ce paiement
     */
    public function destroy($decompteId)
    {
        $res = $this->service->supprimerDecompte($decompteId);
        return response()->json(['data' => $res]);
    }

    /**
     * Créer un fichier iso20022 pour un décompte
     * 
     * @param int $id id du décompte pour lequelle le fichier doit être créé
     */
    public function iso20022($id)
    {
        return $this->service->iso20022PourDecompte($id);
    }

    /**
     * Créer un fichier iso20022 pour un décompte
     * 
     * @param int $id id du décompte pour lequelle le fichier doit être créé
     */
    public function print($id)
    {
        return $this->service->impressionDecompte($id);
    }

    /**
     * Retourne un décompte pour tous les sapeurs
     * 
     * @param int $id id du décompte pour lequelle le fichier doit être créé
     */
    public function printParSapeur($decompteId)
    {
        return $this->service->impressionDecompteParSapeur($decompteId);
    }

    /**
     * Retourne un décompte pour 1 sapeur
     * 
     * @param int $id id du décompte pour lequelle le fichier doit être créé
     */
    public function printPourSapeur($decompteId, $sapeurId)
    {
        return $this->service->impressionDecompteSapeur($decompteId, $sapeurId);
    }

    /**
     * Créer un fichier iso20022 pour un décompte
     * 
     * @param int $id id du décompte pour lequelle le fichier doit être créé
     */
    public function printParCompte($exerciceComptableId)
    {
        return $this->service->impressionDecompteParCompte($exerciceComptableId);
    }

    /**
     * Créer un fichier iso20022 pour un décompte
     * 
     * @param int $id id du décompte pour lequelle le fichier doit être créé
     */
    public function aFacturer($decompteId)
    {
        return $this->service->decompteMontantsAFacturer($decompteId);
    }

    /**
     * Retourne un décompte
     * 
     * @param int $id id du décompte souhaité
     */
    public function show($id)
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
     * Retourne le certificat d'un sapeur pour un exercice comptable
     * 
     * @param int $exerciceComptableId id de l'exercice comp
     * @param int $sapeurId id du sapeur
     */
    public function certificatSalaireSapeur($exerciceComptableId, $sapeurId)
    {
        return $this->service->certificatSalaireSapeur($exerciceComptableId, $sapeurId);
    }

    /**
     * Retourne le certificat de tous les sapeurs pour un exercice comptable
     * 
     * @param int $exerciceComptableId id de l'exercice comp
     */
    public function certificatSalaire($exerciceComptableId)
    {
        return $this->service->certificatSalairePourExerciceComptable($exerciceComptableId);
    }
}
