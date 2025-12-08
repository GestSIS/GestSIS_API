<?php


namespace App\Domaine\API;

use App\Application\Typst\TypstTemplate;
use App\Application\Typst\TypstToPdfGenerator;
use App\Domaine\Business\ImputationBusiness;
use App\Domaine\Business\PaiementBusiness;
use App\Domaine\Business\SisParamBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Domaine\Exceptions\InvalidActionException;
use App\Infrastructure\Collections\AFacturerExport;
use App\Infrastructure\Collections\EcrituresExport;
use App\Infrastructure\Models\AvsParam;
use App\Infrastructure\Models\Compte;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\TypeUnite;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\Paiement;
use App\Infrastructure\Models\Sapeur;
use App\Infrastructure\Models\SisParam;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PaiementService
{
    protected $business;

    public function __construct(PaiementBusiness $business)
    {
        $this->business = $business;
    }

    function getEcrituresPourDecompte($decompteId)
    {
        return Ecriture::where('decompte_id', '=', $decompteId)->get();
    }

    /**
     * Creer un décompte
     * 
     * @param int $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     */
    public function creerDecompteAnnuel($exerciceComptableId, $date, $designation, $selection, $sapeurIds)
    {
        // Vérifi que les paramètres AVS ont été configurés
        $avsParam = AvsParam::first();
        if ($avsParam == NULL) {
            throw new InvalidActionException([], 'Erreur, paramètres AVS manquant, veuillez les compléter dans paramètres.');
        }

        $deduction = true;
        $modules = [];
        if ($selection['ecrituresExercice']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_EXERCICE;
        }
        if ($selection['ecrituresIntervention']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_INTERVENTION;
        }
        if ($selection['ecrituresCours']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_COURS;
        }
        if ($selection['ecrituresDivers']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_DIVERS;
        }
        if ($selection['ecrituresAnnuel']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL;
        }
        if ($selection['ecrituresAmende']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_AMENDE;
        }
        if ($selection['ecrituresTravail']) {
            $modules[] = ImputationBusiness::ECRITURE_MODULE_FICHE_TRAVAIL;
        }

        $ecrituresRequest = Ecriture::whereNull('decompte_id')
            ->where('exercice_comptable_id', $exerciceComptableId)
            ->whereIn('module', $modules);

        if (count($sapeurIds) > 0) {
            $ecrituresRequest = $ecrituresRequest->whereIn('sapeur_id', $sapeurIds);
        }

        $ecritures = $ecrituresRequest->get();
        if ($ecritures->count() === 0) {
            throw new ArrayException([], 'Aucune écriture disponible pour la création du décompte.');
        }

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

        $nomFichier = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", "-", Decompte::find($decompteId)->designation) . ".xml";

        $content = $this->business->iso20022PourDecompte($decompteId, $nom, $bic, $iban);
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            $nomFichier
        );
    }

    public function exportEcritures($decompteId)
    {
        return Excel::download(new EcrituresExport($decompteId), 'ecritures.xlsx');
    }

    public function impressionDecompte($decompteId, $sisKey)
    {
        $decompte = Decompte::find($decompteId);
        $ecritures = Ecriture::where('decompte_id', '=', $decompteId)->orderBy('date')->get();
        $sapeursMap = [];
        $sapeurs = Sapeur::get(['id', 'nom', 'prenom']);
        foreach ($sapeurs as $sapeur) {
            $sapeursMap[$sapeur->id] = "$sapeur->nom $sapeur->prenom";
        }

        $unitesMap = [];
        $unites = TypeUnite::all();
        foreach ($unites as $unite) {
            $unitesMap[$unite->id] = $unite->abreviation;
        }

        $logoPath = (new SisParamBusiness())->getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::Decompte,
            ["decompte" => $decompte, "sapeurs" => $sapeursMap, "ecritures" => $ecritures, "unites" => $unitesMap],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'decompte.pdf'
        );
    }

    public function decompteMontantsAFacturer($decompteId)
    {
        return Excel::download(new AFacturerExport($decompteId), 'a_facturer.xlsx');
    }

    public static function impressionDecompteSapeur($decompteId, $sapeurId, string $sisKey)
    {
        // Pour le moment que les écritures du décompte !
        $ecritures = DB::table('ecritures')
            ->where('ecritures.sapeur_id', '=', $sapeurId)
            ->where('ecritures.decompte_id', '=', $decompteId)
            ->join('sapeurs', 'ecritures.sapeur_id', '=', 'sapeurs.id')
            ->join('ecriture_categories', 'ecritures.ecriture_categorie_id', '=', 'ecriture_categories.id')
            ->join('type_unites', 'ecritures.type_unite_id', '=', 'type_unites.id')
            ->join('civilites', 'sapeurs.civilite_id', '=', 'civilites.id')
            ->select(
                'ecritures.*',
                DB::raw('CONCAT(sapeurs.nom, " ", sapeurs.prenom) as sapeur'),
                'sapeurs.iban',
                'ecriture_categories.tri',
                'ecriture_categories.designation AS categorie',
                'type_unites.abreviation as unite',
                'civilites.forme_politesse as civilite'
            )
            ->orderBy('sapeur')
            ->orderBy('ecriture_categories.tri', 'ASC')
            ->orderBy('ecritures.module', 'ASC')
            ->orderBy('ecritures.date')
            ->orderBy('ecritures.heure')
            ->get();

        return self::printDecompteSapeur($decompteId, $ecritures, $sisKey, false);
    }

    public static function impressionDecompteParSapeur($decompteId, string $sisKey)
    {
        // Pour le moment que les écritures du décompte !
        $ecritures = DB::table('ecritures')
            ->join('sapeurs', 'ecritures.sapeur_id', '=', 'sapeurs.id')
            ->join('ecriture_categories', 'ecritures.ecriture_categorie_id', '=', 'ecriture_categories.id')
            ->join('type_unites', 'ecritures.type_unite_id', '=', 'type_unites.id')
            ->join('civilites', 'sapeurs.civilite_id', '=', 'civilites.id')
            ->where('ecritures.decompte_id', '=', $decompteId)
            ->select(
                'ecritures.*',
                DB::raw('CONCAT(sapeurs.nom, " ", sapeurs.prenom) as sapeur'),
                'sapeurs.iban',
                'ecriture_categories.tri',
                'ecriture_categories.designation AS categorie',
                'type_unites.abreviation as unite',
                'civilites.forme_politesse as civilite'
            )
            ->orderBy('sapeur')
            ->orderBy('ecriture_categories.tri', 'ASC')
            ->orderBy('ecritures.module', 'ASC')
            ->orderBy('ecritures.date')
            ->orderBy('ecritures.heure')
            ->get();

        return self::printDecompteSapeur($decompteId, $ecritures, $sisKey, true);
    }

    public static function impressionResumePourSapeur(int $exerciceComptableId, int $sapeurId, string $sisKey)
    {
        // Pour le moment que les écritures du décompte !
        $ecritures = DB::table('ecritures')
            ->join('sapeurs', 'ecritures.sapeur_id', '=', 'sapeurs.id')
            ->join('ecriture_categories', 'ecritures.ecriture_categorie_id', '=', 'ecriture_categories.id')
            ->join('type_unites', 'ecritures.type_unite_id', '=', 'type_unites.id')
            ->join('civilites', 'sapeurs.civilite_id', '=', 'civilites.id')
            ->join('decomptes', 'ecritures.decompte_id', '=', 'decomptes.id')
            ->where('decomptes.exercice_comptable_id', '=', $exerciceComptableId)
            ->where('sapeurs.id', '=', $sapeurId)
            ->select(
                'ecritures.*',
                DB::raw('CONCAT(sapeurs.nom, " ", sapeurs.prenom) as sapeur'),
                'sapeurs.iban',
                'ecriture_categories.tri',
                'ecriture_categories.designation AS categorie',
                'type_unites.abreviation as unite',
                'civilites.forme_politesse as civilite'
            )
            ->orderBy('ecriture_categories.tri', 'ASC')
            ->orderBy('ecritures.module', 'ASC')
            ->orderBy('ecritures.date')
            ->orderBy('ecritures.heure')
            ->get();

        return self::printResumeSapeur($exerciceComptableId, $ecritures, $sisKey);
    }

    public static function impressionResumeParSapeur(int $exerciceComptableId, string $sisKey)
    {
        // Pour le moment que les écritures du décompte !
        $ecritures = DB::table('ecritures')
            ->join('sapeurs', 'ecritures.sapeur_id', '=', 'sapeurs.id')
            ->join('ecriture_categories', 'ecritures.ecriture_categorie_id', '=', 'ecriture_categories.id')
            ->join('type_unites', 'ecritures.type_unite_id', '=', 'type_unites.id')
            ->join('civilites', 'sapeurs.civilite_id', '=', 'civilites.id')
            ->join('decomptes', 'ecritures.decompte_id', '=', 'decomptes.id')
            ->where('decomptes.exercice_comptable_id', '=', $exerciceComptableId)
            ->select(
                'ecritures.*',
                DB::raw('CONCAT(sapeurs.nom, " ", sapeurs.prenom) as sapeur'),
                'sapeurs.iban',
                'ecriture_categories.tri',
                'ecriture_categories.designation AS categorie',
                'type_unites.abreviation as unite',
                'civilites.forme_politesse as civilite'
            )
            ->orderBy('sapeur')
            ->orderBy('ecriture_categories.tri', 'ASC')
            ->orderBy('ecritures.module', 'ASC')
            ->orderBy('ecritures.date')
            ->orderBy('ecritures.heure')
            ->get();

        return self::printResumeSapeur($exerciceComptableId, $ecritures, $sisKey);
    }

    private static function printResumeSapeur(int $exerciceComptableId, $ecritures, string $sisKey)
    {
        $decomptes = Decompte::with('paiements')->where('exercice_comptable_id', $exerciceComptableId)->get();
        $decomptesMap = [];
        foreach ($decomptes as $d) {
            $decomptesMap[$d->id] = $d;
        }

        $comptes = Compte::all();
        $comptesMap = [];
        foreach ($comptes as $compte) {
            $comptesMap[$compte->id] = $compte;
        }

        $sapeursMap = [];
        $sapeurs = Sapeur::get(['id', 'nom', 'prenom']);
        foreach ($sapeurs as $sapeur) {
            $sapeursMap[$sapeur->id] = "$sapeur->nom $sapeur->prenom";
        }

        $logoPath = (new SisParamBusiness())->getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::ResumeParSapeur,
            ["decomptes" => $decomptesMap, "sapeurs" => $sapeursMap, "ecritures" => $ecritures, "comptes" => $comptesMap],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'resume-par-sapeur.pdf'
        );
    }

    private static function printDecompteSapeur($decompteId, $ecritures, string $sisKey, bool $resume = false)
    {
        $decompte = Decompte::with('paiements')->find($decompteId);
        $decomptes = Decompte::where('exercice_comptable_id', $decompte->exercice_comptable_id)->get();
        $decomptesMap = [];
        foreach ($decomptes as $d) {
            $decomptesMap[$d->id] = $d;
        }

        $comptes = Compte::all();
        $comptesMap = [];
        foreach ($comptes as $compte) {
            $comptesMap[$compte->id] = $compte;
        }

        $sapeursMap = [];
        $sapeurs = Sapeur::get(['id', 'nom', 'prenom']);
        foreach ($sapeurs as $sapeur) {
            $sapeursMap[$sapeur->id] = "$sapeur->nom $sapeur->prenom";
        }

        $logoPath = (new SisParamBusiness())->getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::DecompteParSapeur,
            ["decompte" => $decompte, "decomptes" => $decomptesMap, "sapeurs" => $sapeursMap, "ecritures" => $ecritures, "comptes" => $comptesMap, 'resume' => $resume],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'decompte-par-sapeur.pdf'
        );
    }

    public function impressionDecompteParCompte($decompteId)
    {
        // TODO: à implémenter
        throw new ArrayException([], "Non implémenté pour le moment");
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
        // try {
        $content = $this->business->iso20022PourPaiement($paiementId, $nom, $bic, $iban);
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            $nomFichier
        );
        // } catch (\InvalidArgumentException $e) {
        //     return response()->json(['data' => ['message' => 'Veuillez vérifier les informations de paiement de votre SIS']], 500);
        // }
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
