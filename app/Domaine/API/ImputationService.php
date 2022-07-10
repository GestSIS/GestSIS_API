<?php

namespace App\Domaine\API;

use App\Domaine\Business\ImputationBusiness;
use App\Domaine\SPI\EcritureRepository;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\SPI\IndemniteTypeRepository;
use App\Infrastructure\Models\Compte;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\Sapeur;

class ImputationService
{
    protected $ecritureRepo;
    protected $exerciceRepo;
    protected $indemniteRepo;
    protected $compteRepo;
    protected $business;

    public function __construct(
        EcritureRepository $ecriture,
        ExerciceRepository $exercice,
        IndemniteTypeRepository $indemnite,
        ImputationBusiness $business
    ) {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->indemniteRepo = $indemnite;
        $this->business = $business;
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
        return $this->ecritureRepo->listeAllEcritureForExerciceComptableById($exerciceComptableId);
    }

    function getEcrituresDiversForExerciceComptableById($exerciceComptableId)
    {
        return $this->ecritureRepo->listeEcritureDiversForExerciceComptableById($exerciceComptableId);
    }

    function getEcrituresAmendesForExerciceComptableById($exerciceComptableId)
    {
        return $this->ecritureRepo->listeAmendeForExerciceComptableById($exerciceComptableId);
    }

    function getEcrituresByCompte($compteId, $exerciceComptableId)
    {
        return $this->ecritureRepo->listeEcritureForCompteAndExerciceComptableById($compteId, $exerciceComptableId);
    }

    function getEcrituresForExerciceById($exerciceId)
    {
        return $this->ecritureRepo->listeEcritureForExercice($exerciceId);
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
        return $this->ecritureRepo->listeEcritureForIntervention($interventionId);
    }

    function getEcrituresAnnuelsForExerciceComptableById($exerciceComptableId)
    {
        return $this->ecritureRepo->listeEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);
    }

    function imputationExercice($exerciceId, $data)
    {
        $statut = $this->business->imputerExercice($exerciceId, $data);

        return [
            "statut" => $statut,
            "ecritures" => $this->ecritureRepo->listeEcritureForExercice($exerciceId)
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
            "ecritures" => $this->ecritureRepo->listeEcritureForIntervention($interventionId)
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

        return $this->ecritureRepo->listeEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);
    }

    function genererAmendesSapeur($exerciceComptableId, $sapeurId)
    {
        return $this->business->genererAmendesSapeur($exerciceComptableId, $sapeurId);
    }

    function genererAmendeAnnuel($exerciceComptableId)
    {
        return $this->business->genererAmendesAnnuels($exerciceComptableId);
    }

    function decompteAnnuelParSapeur($exerciceComptableId)
    {
        $ecritures = $this->ecritureRepo->computeEcritureForPersonalDecompte($exerciceComptableId);

        return View('pdf/decomptes-sapeurs', ["ecritures" => $ecritures]);
    }

    public function justificatifIndividuel(int $exerciceComptableId, int $compteId)
    {
        // Blade::component('single-compte', SingleCompte::class);

        $compte = Compte::with(['ecritures' => function ($query) use ($exerciceComptableId) {
            $query->where('exercice_comptable_id', $exerciceComptableId)->orderBy('date', 'asc');
        }])->find($compteId);

        // Chargement des groupes
        $sapeursMap = [];
        $sapeurs = Sapeur::get(['id', 'nom', 'prenom']);
        foreach ($sapeurs as $sapeur) {
            $sapeursMap[$sapeur->id] = "$sapeur->nom $sapeur->prenom";
        }

        return View('pdf/compte', [
            "compte" => $compte,
            "sapeurs" => $sapeursMap,
        ]);
    }

    public function justificatifComplet(int $exerciceComptableId)
    {
        $comptes = Compte::with(['ecritures' => function ($query) use ($exerciceComptableId) {
            $query->where('exercice_comptable_id', $exerciceComptableId)->orderBy('date', 'asc');
        }])->orderBy('numero', 'asc')->get();

        // Chargement des groupes
        $sapeursMap = [];
        $sapeurs = Sapeur::get(['id', 'nom', 'prenom']);
        foreach ($sapeurs as $sapeur) {
            $sapeursMap[$sapeur->id] = "$sapeur->nom $sapeur->prenom";
        }

        return View('pdf/comptes', [
            "comptes" => $comptes,
            "sapeurs" => $sapeursMap,
        ]);
    }
}
