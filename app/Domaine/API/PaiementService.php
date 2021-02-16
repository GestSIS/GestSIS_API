<?php


namespace App\Domaine\API;

use App\Domaine\Business\PaiementBusiness;
use App\Infrastructure\Models\AvsParam;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Paiement;
use App\Infrastructure\Models\SisParam;

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
     * @param string $designation - nom du décompte
     * @param int $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     * @param float $taux_avs - taux avs payé par le sapeur
     * @param float $taux_ac - taux ac payé par le sapeur
     * @param boolean $deduction - true si les déduction doivent être faites sur ce paiement
     * @param float $franchiseAvs - montant imposable minimum pour l'avs
     * @param float $franchiseImposition - montant minimum pour que la solde soit imposable
     */
    public function creerDecompte($exerciceComptableId, $deduction)
    {
        $designation = 'Decompte n°xxx';
        $params = AvsParam::first();
        
        $tauxAc = $params->taux_ac;
        $tauxAvs = $params->taux_avs;
        $franchiseImposition = $params->franchise_imposition;
        $franchiseAvs = $params->franchise_avs;
        
        return $this->business->creerDecompte($designation, $exerciceComptableId, $deduction, $tauxAc, $tauxAvs, $franchiseImposition, $franchiseAvs);
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

        return response()->streamDownload(
            fn () =>
            $this->business->iso20022FromDecompte($decompteId, $nom, $bic, $iban),
            $nomFichier
        );
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

        // TODO: Tester
        return response()->streamDownload(
            function () use ($paiementId, $nom, $bic, $iban) {
                echo $this->business->iso20022FromPaiement($paiementId, $nom, $bic, $iban);
            },
            $nomFichier
        );
        // return response()->streamDownload(function () use ($data, $id) {
        //     echo $this->business->iso20022FromPaiement($id, $data['nom'], $data['bic'], $data['iban']);
        // }, "paiement.xml");
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
