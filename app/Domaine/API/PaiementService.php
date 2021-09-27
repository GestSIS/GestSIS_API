<?php


namespace App\Domaine\API;

use App\Domaine\Business\PaiementBusiness;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\Paiement;
use App\Infrastructure\Models\Sapeur;
use App\Infrastructure\Models\SisParam;
use Barryvdh\Snappy\Facades\SnappyPdf;

class PaiementService
{
    protected $ecritureRepo;
    protected $exerciceRepo;
    protected $indemniteRepo;
    protected $fraisRepo;
    protected $compteRepo;
    protected $business;

    public function __construct(PaiementBusiness $business)
    {
        $this->business = $business;
    }

    /**
     * creer un décompte
     * 
     * @param int $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     */
    public function creerDecompteAnnuel($exerciceComptableId, $date, $designation)
    {
        $deduction = true;
        $ecritures = Ecriture::where('exercice_comptable_id', $exerciceComptableId)->get();

        return $this->business->creerDecompte($ecritures, $designation, $exerciceComptableId, $date, $deduction);
    }

    /**
     * supprimer un décompte
     * 
     * @param int $edecompteId - id du décompte à supprimer
     */
    public function supprimerDecompte($decompteId)
    {
        return $this->business->supprimerDecompte($decompteId);
    }

    /**
     * creer un décompte
     * 
     * @param int $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     */
    public function creerDecompteSapeur($exerciceComptableId, $sapeurId, $date)
    {
        $sapeur = Sapeur::find($sapeurId);
        $designation = "Decompte $sapeur->nom $sapeur->prenom";

        $deduction = true;

        $ecritures = Ecriture::where([
            ['exercice_comptable_id', '=', $exerciceComptableId],
            ['sapeur_id', '=', $sapeurId],
        ])->get();

        return $this->business->creerDecompte($ecritures, $designation, $exerciceComptableId, $date, $deduction);
    }

    /**
     * creer un décompte
     * 
     * @param int $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     */
    public function creerDecompteExercice($exerciceId, $date, $deduction)
    {
        $designation = 'Decompte exercice';
        $exerciceComptableId = Exercice::find($exerciceId)->exercice_comptable_id;
        $ecritures = Ecriture::where('exercice_id', $exerciceId)->get();

        return $this->business->creerDecompte($ecritures, $designation, $exerciceComptableId, $date, $deduction);
    }

    /**
     * Créer un fichier iso20022 pour un décompte
     * 
     * @param int $id id du décompte pour lequelle le fichier doit être créé
     * @param string $nom titulaire du compte débiteur
     * @param string $bic bic de la banque du compte débiteur
     * @param string $iban iban du compte débiteur
     */
    public function iso20022PourDecompte($decompteId)
    {
        $params = SisParam::first();
        $nom = $params->nom;
        $bic = $params->bic;
        $iban = $params->iban;

        $nomFichier = Decompte::find($decompteId)->designation . ".xml";
        try {
            $content = $this->business->iso20022PourDecompte($decompteId, $nom, $bic, $iban);
            return response()->streamDownload(
                function () use ($content) {
                    echo $content;
                },
                $nomFichier
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['data' => ['message' => 'Veuillez vérifier les informations de paiment de votre SIS']], 500);
        }
    }

    public function impressionDecompte($decompteId)
    {
        $decompte = Decompte::find($decompteId);
        $ecritures = Ecriture::where('decompte_id', '=', $decompteId)->orderBy('date')->get();
        $sapeursMap = [];
        $sapeurs = Sapeur::get(['id', 'nom', 'prenom']);
        foreach ($sapeurs as $sapeur) {
            $sapeursMap[$sapeur->id] = "$sapeur->nom $sapeur->prenom";
        }

        return View('pdf/decompte', ["decompte" => $decompte, "sapeurs" => $sapeursMap, "ecritures" => $ecritures]);
        $pdf = SnappyPdf::loadView('pdf/decompte', ["decompte" => $decompte, "sapeurs" => $sapeursMap, "ecritures" => $ecritures]);
        return $pdf->download('presences.pdf');
    }

    /**
     * Retourne un décompte
     * 
     * @param int $id du décompte souhaité
     */
    public function getDecompteParId($id)
    {
        return Decompte::where('id', $id)->with('paiements')->first();
    }

    /**
     * Retourne tous les décompte pour un exercice comptable
     * 
     * @param int $id id de l'exercice comptable
     */
    public function getDecomptePourExerciceComptable($id)
    {
        return Decompte::where('exercice_comptable_id', $id)->get();
    }

    /**
     * Retourne le certificat d'un sapeur pour un exercice comptable
     * 
     * @param int $exerciceComptableId id de l'exercice comp
     * @param int $sapeurId id du sapeur
     */
    public function certificatSalaireSapeur($exerciceComptableId, $sapeurId)
    {
        $affichageFrais = true;
        return $this->business->certificatSalaireSapeur($exerciceComptableId, $sapeurId, $affichageFrais);
    }

    /**
     * Retoune le certificat de tous les sapeurs pour un exercice comptable
     * 
     * @param int $exerciceComptableId id de l'exercice comp
     */
    public function certificatSalairePourExerciceComptable($exerciceComptableId)
    {
        $affichageFrais = true;
        return $this->business->certificatSalaire($exerciceComptableId, $affichageFrais);
    }

    /**
     * Créer un fichier iso20022 pour un paiement
     * 
     * @param int $id id du paiement pour lequelle le fichier doit être créé
     * @param string $nom titulaire du compte débiteur
     * @param string $bic bic de la banque du compte débiteur
     * @param string $iban iban du compte débiteur
     */
    public function iso20022PourPaiementSapeur($paiementId)
    {
        $params = SisParam::first();
        $nom = $params->nom;
        $bic = $params->bic;
        $iban = $params->iban;

        $nomFichier = "paiement.xml";
        try {
            $content = $this->business->iso20022PourPaiement($paiementId, $nom, $bic, $iban);
            return response()->streamDownload(
                function () use ($content) {
                    echo $content;
                },
                $nomFichier
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['data' => ['message' => 'Veuillez vérifier les informations de paiement de votre SIS']], 500);
        }
    }

    /**
     * Retourne un paiement
     * $id - id du paiement souhaité
     */
    public function getPaiementSapeurParId($id)
    {
        return Paiement::find($id);
    }

    /**
     * Retourne tous les paiements pour un exercice comptable
     * $id - id de l'exercice comptable
     */
    public function getPaiementsPourExerciceComptable($id)
    {
        return Decompte::where('exercice_comptable_id', $id)->with('paiements')->get();
    }
}
