<?php


namespace App\Domaine\API;

use App\Application\Mail\DecompteSapeur;
use App\Domaine\Business\ImputationBusiness;
use App\Domaine\Business\PaiementBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Collections\AFacturerExport;
use App\Infrastructure\Models\AvsParam;
use App\Infrastructure\Models\Compte;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\Paiement;
use App\Infrastructure\Models\Sapeur;
use App\Infrastructure\Models\SisParam;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

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
     * creer un décompte
     * 
     * @param int $exerciceComptableId - id de l'exercice comptable pour lequel créer les paiements
     */
    public function creerDecompteAnnuel($exerciceComptableId, $date, $designation, $selection)
    {
        // Vérifi que les paramètres AVS ont été configurés
        $avsParam = AvsParam::first();
        if ($avsParam == NULL) {
            throw new ArrayException([], 'Erreur, paramètres AVS manquant, veuillez les compléter dans paramètres.');
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

        $ecritures = Ecriture::whereNull('decompte_id')->where('exercice_comptable_id', $exerciceComptableId)->whereIn('module', $modules)->get();
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
            throw new ArrayException([], 'Veuillez vérifier les informations de paiement de votre SIS');
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
    }

    public function decompteMontantsAFacturer($decompteId)
    {
        return Excel::download(new AFacturerExport($decompteId), 'a_facturer.xlsx');
    }

    public function envoyerDecompteSapeurs($decompteId, $email, $texte, $sapeurIds, $token, $sisId)
    {
        $officialSapeurIds = Paiement::where('decompte_id', '=', $decompteId)->pluck('sapeur_id')->toArray();
        $validatedSapeurIds = array_intersect($officialSapeurIds, $sapeurIds);

        $url = config('gestsis.print_url') . "/api/v1/print";

        $responses = Http::pool(function (Pool $pool) use ($decompteId, $validatedSapeurIds, $url, $token, $sisId) {
            return collect($validatedSapeurIds)
                ->map(fn ($sapeurId) => $pool->withToken($token)->withHeaders(['Sis-Id' => $sisId])->post($url, [
                    'content' => $this->impressionDecompteSapeur($decompteId, $sapeurId)->render(),
                ]));
        });
        foreach (array_values($validatedSapeurIds) as $index => $sapeurId) {
            $sapeur = Sapeur::find($sapeurId);
            $response = $responses[$index];
            // throw new ArrayException(['content' => $response->body()]);
            return             response($response->body())->header('Content-Type', 'application/pdf');
            // return "Test " . $response->body();
            if ($response instanceof Throwable) {
                Log::error($sapeurId . " - " . $response);
                continue;
            }
            // return $response->body();
            Log::debug($sapeurId);
            $pdf = $responses[$index]->body();

            $res = Mail::to($sapeur->email)->send(new DecompteSapeur($sapeur, $pdf, $email));
        }
        return 'OK';
    }

    public static function impressionDecompteSapeur($decompteId, $sapeurId)
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

        return self::printDecompteSapeur($decompteId, $ecritures);
    }

    public function impressionDecompteParSapeur($decompteId)
    {
        // Pour le moment que les écritures du décompte !
        $ecritures = DB::table('ecritures')
            ->join('sapeurs', 'ecritures.sapeur_id', '=', 'sapeurs.id')
            ->join('ecriture_categories', 'ecritures.ecriture_categorie_id', '=', 'ecriture_categories.id')
            ->join('type_unites', 'ecritures.type_unite_id', '=', 'type_unites.id')
            ->join('civilites', 'sapeurs.civilite_id', '=', 'civilites.id')
            ->where('ecritures.decompte_id', $decompteId)
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

        return $this->printDecompteSapeur($decompteId, $ecritures);
    }

    private static function printDecompteSapeur($decompteId, $ecritures)
    {
        $decompte = Decompte::with('paiements')->find($decompteId);
        $decomptes = Decompte::where('exercice_comptable_id', $decompte->exercice_comptable_id)->get();
        $decomptesMap = [];
        foreach ($decomptes as $decompte) {
            $decomptesMap[$decompte->id] = $decompte;
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
        return View('pdf/decomptes-sapeurs', ["decompte" => $decompte, "decomptes" => $decomptesMap, "sapeurs" => $sapeursMap, "ecritures" => $ecritures, "comptes" => $comptesMap]);
    }

    public function impressionDecompteParCompte($decompteId)
    {
        $decompte = Decompte::find($decompteId);
        $ecritures = Ecriture::where('decompte_id', '=', $decompteId)->orderBy('date')->get();
        $sapeursMap = [];
        $sapeurs = Sapeur::get(['id', 'nom', 'prenom']);
        foreach ($sapeurs as $sapeur) {
            $sapeursMap[$sapeur->id] = "$sapeur->nom $sapeur->prenom";
        }

        return View('pdf/decompte', ["decompte" => $decompte, "sapeurs" => $sapeursMap, "ecritures" => $ecritures]);
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
