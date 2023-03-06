<?php


namespace App\Domaine\API;

use App\Domaine\Business\ExerciceBusiness;
use App\Domaine\Business\PaiementBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\HeureExercice;
use App\Infrastructure\Models\InterventionSapeur;
use App\Infrastructure\Models\MaterielPersonnel;
use App\Infrastructure\Models\Paiement;
use App\Infrastructure\Models\Travail;
use Illuminate\Support\Facades\DB;

class MesInfosService
{
    protected $paiementBusiness;
    protected $exerciceBusiness;
    protected $exerciceRepo;
    protected $sapeurRepo;

    public function __construct(PaiementBusiness $paiementBusiness, ExerciceBusiness $exerciceBusiness, ExerciceRepository $exerciceRepo, SapeurRepository $sapeurRepo)
    {
        $this->paiementBusiness = $paiementBusiness;
        $this->exerciceBusiness = $exerciceBusiness;
        $this->exerciceRepo = $exerciceRepo;
        $this->sapeurRepo = $sapeurRepo;
    }

    function mesInfos($sapeurId)
    {
        return $this->sapeurRepo->getSapeurDetailsById($sapeurId, ['telephones']);
    }

    function mesFonctions($sapeurId)
    {
        return $this->sapeurRepo->getSapeurFonctionsById($sapeurId);
    }

    function mesMutations($sapeurId)
    {
        return $this->sapeurRepo->getSapeurMutationsById($sapeurId);
    }

    function mesGrades($sapeurId)
    {
        return $this->sapeurRepo->getSapeurGradesById($sapeurId);
    }

    function mesCours($sapeurId)
    {
        return $this->sapeurRepo->getSapeurCoursById($sapeurId);
    }

    function mesGroupes($sapeurId)
    {
        return $this->sapeurRepo->getSapeurGroupesbyId($sapeurId);
    }

    function mesExercices($sapeurId, $exerciceComptableId)
    {
        return $this->exerciceRepo->listExerciceOfSapeurById($exerciceComptableId, $sapeurId);
    }

    function creerExcuse($sapeurId, $exerciceId, $data, $file, $sisKey)
    {
        return $this->exerciceBusiness->creerExcuse($sapeurId, $exerciceId, $data, $file, $sisKey);
    }

    function removeExcuse($sapeurId, $exerciceId, $hasValidationPermission)
    {
        return $this->exerciceBusiness->removeExcuse($sapeurId, $exerciceId, $hasValidationPermission);
    }

    function getJustificatif($exerciceId, $sapeurId)
    {
        $presence = ExerciceSapeur::where([['exercice_id', '=', $exerciceId], ['sapeur_id', '=', $sapeurId]])->first();
        if ($presence == null || !$presence->justificatif_filename) {
            throw new ArrayException([], "Aucun justificatif !");
        }

        return ['path' => $presence->justificatif_path, 'filename' => $presence->justificatif_filename];
    }

    function monMateriel($sapeurId)
    {
        return MaterielPersonnel::with('materiel')->where('sapeur_id', '=', $sapeurId)->get()->toArray();
    }

    function mesTravaux($sapeurId, $exerciceComptableId)
    {
        return Travail::where([
            ['sapeur_id', '=', $sapeurId],
            ['exercice_comptable_id', '=', $exerciceComptableId]
        ])->get();
    }

    function mesInterventions($sapeurId, $exerciceComptableId)
    {
        $presences = InterventionSapeur::where('intervention_sapeur.sapeur_id', '=', $sapeurId)
            ->join('interventions', 'interventions.id', '=', 'intervention_sapeur.intervention_id')
            ->where('interventions.exercice_comptable_id', '=', $exerciceComptableId)
            ->select(
                'intervention_sapeur.*',
                'interventions.date_debut',
                'interventions.heure_debut',
                'interventions.date_fin',
                'interventions.heure_fin',
                'interventions.lieu',
                'interventions.objet',
                'interventions.localite_id',
                'interventions.stat_federal_id',
                'interventions.type_intervention_id',
            )->get()->toArray();
        return $presences;
    }

    function mesDecomptes($sapeurId, $exerciceComptableId)
    {
        $paiements = Paiement::where('sapeur_id', '=', $sapeurId)
            ->join('decomptes', 'paiements.decompte_id', '=', 'decomptes.id')
            ->where('decomptes.exercice_comptable_id', '=', $exerciceComptableId)
            ->select('paiements.*', 'decomptes.date as date', 'decomptes.designation as decompte')->get();
        $ecritures = Ecriture::where('sapeur_id', '=', $sapeurId)->whereNotNull('decompte_id')->get();

        return [
            'paiements' => $paiements,
            'ecritures' => $ecritures,
        ];
    }

    function printDecompte($sapeurId, $decompteId)
    {
        return PaiementService::impressionDecompteSapeur($decompteId, $sapeurId);
    }
}
