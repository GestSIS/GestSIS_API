<?php

namespace App\Domaine\API;

use App\Application\Typst\TypstTemplate;
use App\Application\Typst\TypstToPdfGenerator;
use App\Domaine\Business\ImputationBusiness;
use App\Domaine\Business\SisParamBusiness;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\SPI\IndemniteTypeRepository;
use App\Infrastructure\Models\Compte;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\Sapeur;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImputationService
{
    protected $exerciceRepo;
    protected $indemniteRepo;
    protected $compteRepo;
    protected $business;

    public function __construct(
        ExerciceRepository $exercice,
        IndemniteTypeRepository $indemnite,
        ImputationBusiness $business
    ) {
        $this->exerciceRepo = $exercice;
        $this->indemniteRepo = $indemnite;
        $this->business = $business;
    }

    function statCompte($exerciceComptableId)
    {
        $data = DB::select("SELECT e.compte_id, count(e.id) AS nb, sum(e.total) AS total
                FROM ecritures AS e
                WHERE e.exercice_comptable_id = ?
                GROUP BY e.compte_id
            ", [$exerciceComptableId]);

        return $data;
    }

    function statCategorie($exerciceComptableId)
    {
        $data = DB::select("SELECT e.ecriture_categorie_id, count(e.id) AS nb, sum(CASE
                    WHEN c.produit THEN -e.total
                    ELSE e.total
                END) AS total
                FROM ecritures AS e
                INNER JOIN comptes AS c ON c.id = e.compte_id
                WHERE e.exercice_comptable_id = ?
                GROUP BY e.ecriture_categorie_id
            ", [$exerciceComptableId]);

        return $data;
    }

    function statModule($exerciceComptableId)
    {
        $data = DB::select("SELECT e.module, count(e.id) AS nb, sum(CASE
                    WHEN c.produit THEN -e.total
                    ELSE e.total
                END) AS total
                FROM ecritures AS e
                INNER JOIN comptes AS c ON c.id = e.compte_id
                WHERE e.exercice_comptable_id = ?
                GROUP BY e.module
            ", [$exerciceComptableId]);

        return $data;
    }

    function ajouterEcriture($data)
    {
        return $this->business->ajouterEcriture($data);
    }

    function modifierEcriture($ecritureId, $data)
    {
        return $this->business->modifierEcriture($ecritureId, $data);
    }

    function supprimerEcriture($ecritureId)
    {
        return $this->business->supprimerEcriture($ecritureId);
    }

    function creerExerciceComptable($data)
    {
        return $this->business->creerExerciceComptable($data);
    }

    function getAllEcrituresForExerciceComptableById($exerciceComptableId)
    {
        return Ecriture::where('exercice_comptable_id', $exerciceComptableId)->get();
    }

    function getEcrituresDiversForExerciceComptableById($exerciceComptableId)
    {
        return Ecriture::where('exercice_comptable_id', $exerciceComptableId)
            ->where('module', ImputationBusiness::ECRITURE_MODULE_DIVERS)
            ->get();
    }

    function getEcrituresAmendesForExerciceComptableById($exerciceComptableId)
    {
        return Ecriture::where('exercice_comptable_id', $exerciceComptableId)
            ->where('module', ImputationBusiness::ECRITURE_MODULE_AMENDE)
            ->get();
    }

    function getEcrituresByCompte($compteId, $exerciceComptableId)
    {
        return Ecriture::where('exercice_comptable_id', $exerciceComptableId)
            ->where('compte_id', $compteId)
            ->get();
    }

    function getEcrituresForExerciceById($exerciceId)
    {
        return Ecriture::where('exercice_id', $exerciceId)->get();
    }

    function getEcrituresForExercicesByExerciceComptable($exerciceComptableId)
    {
        return Exercice::where([
            ['exercice_comptable_id', '=', $exerciceComptableId],
            ['statut', '>', 2],
        ])->with('ecritures')->get();
    }

    function getEcrituresForInterventionById($interventionId)
    {
        return Ecriture::where('intervention_id', $interventionId)->get();
    }

    function getEcrituresAnnuelsForExerciceComptableById($exerciceComptableId)
    {
        return Ecriture::where('exercice_comptable_id', $exerciceComptableId)
            ->where('module', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL)
            ->get();
    }

    function imputationExercice($exerciceId, $data)
    {
        $statut = $this->business->imputerExercice($exerciceId, $data);

        return [
            "statut" => $statut,
            "ecritures" => Ecriture::where('exercice_id', $exerciceId)->get()
        ];
    }

    function annulerImputationExercice($exerciceId)
    {
        $statut = $this->business->annulerImputationExercice($exerciceId);
        return [
            "statut" => $statut,
        ];
    }

    function imputationIntervention($interventionId, $data)
    {
        $statut = $this->business->imputerIntervention($interventionId, $data);

        return [
            "statut" => $statut,
            "ecritures" => Ecriture::where('intervention_id', $interventionId)->get()
        ];
    }

    function annulerImputationIntervention($interventionId)
    {
        $statut = $this->business->annulerImputationIntervention($interventionId);
        return [
            "statut" => $statut,
        ];
    }

    function imputationAnnuel($exerciceComptableId)
    {
        $this->business->imputerAnnuel($exerciceComptableId);

        return Ecriture::where('exercice_comptable_id', $exerciceComptableId)
            ->where('module', ImputationBusiness::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL)
            ->get();
    }

    function annulerImputationAnnuel($exerciceComptableId)
    {
        return $this->business->annulerImputationAnnuel($exerciceComptableId);
    }

    function imputationCours($coursSapeurId, $data)
    {
        return $this->business->imputerCours($coursSapeurId, $data);
    }

    function annulerImputationCours($coursSapeurId)
    {
        return $this->business->annulerImputationCours($coursSapeurId);
    }

    function imputationTravail($ids)
    {
        return $this->business->imputerTravaux($ids);
    }

    function annulerImputationTravail($coursSapeurId)
    {
        return $this->business->annulerImputationTravail($coursSapeurId);
    }

    function genererAmendesSapeur($exerciceComptableId, $sapeurId)
    {
        return $this->business->genererAmendesSapeur($exerciceComptableId, $sapeurId);
    }

    function genererAmendeAnnuel($exerciceComptableId)
    {
        return $this->business->genererAmendesAnnuels($exerciceComptableId);
    }

    public function justificatifIndividuel(int $exerciceComptableId, int $compteId, string $sisKey)
    {
        $compte = Compte::with([
            'ecritures' => function ($query) use ($exerciceComptableId) {
                $query->where('exercice_comptable_id', $exerciceComptableId)->orderBy('date', 'asc');
            }
        ])->find($compteId);

        $sapeursMap = [];
        $sapeurs = Sapeur::get(['id', 'nom', 'prenom']);
        foreach ($sapeurs as $sapeur) {
            $sapeursMap[$sapeur->id] = "$sapeur->nom $sapeur->prenom";
        }

        $decomptesMap = [];
        $decomptes = Decompte::where('exercice_comptable_id', $exerciceComptableId)->get(['id', 'date']);
        foreach ($decomptes as $decompte) {
            $decomptesMap[$decompte->id] = $decompte->date;
        }


        $logoPath = (new SisParamBusiness())->getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::Comptes,
            [
                "date" => Carbon::now(),
                "comptes" => [$compte],
                "sapeurs" => $sapeursMap,
                "decomptes" => $decomptesMap,
            ],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'justificatif_complet.pdf'
        );
    }

    public function justificatifComplet(int $exerciceComptableId, string $sisKey)
    {
        $comptes = Compte::with([
            'ecritures' => function ($query) use ($exerciceComptableId) {
                $query->where('exercice_comptable_id', $exerciceComptableId)->orderBy('date', 'asc');
            }
        ])->orderBy('numero', 'asc')->get();

        // Chargement des groupes
        $sapeursMap = [];
        $sapeurs = Sapeur::get(['id', 'nom', 'prenom']);
        foreach ($sapeurs as $sapeur) {
            $sapeursMap[$sapeur->id] = "$sapeur->nom $sapeur->prenom";
        }

        $decomptesMap = [];
        $decomptes = Decompte::where('exercice_comptable_id', $exerciceComptableId)->get(['id', 'date']);
        foreach ($decomptes as $decompte) {
            $decomptesMap[$decompte->id] = $decompte->date;
        }

        $logoPath = (new SisParamBusiness())->getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::Comptes,
            [
                "date" => Carbon::now(),
                "comptes" => $comptes,
                "sapeurs" => $sapeursMap,
                "decomptes" => $decomptesMap,
            ],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'justificatif_complet.pdf'
        );
    }
}
