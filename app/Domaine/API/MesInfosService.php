<?php


namespace App\Domaine\API;

use App\Domaine\Business\PaiementBusiness;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\InterventionSapeur;
use App\Infrastructure\Models\Paiement;
use Illuminate\Support\Facades\DB;

class MesInfosService
{
    protected $paiementBusiness;

    public function __construct(PaiementBusiness $paiementBusiness)
    {
        $this->paiementBusiness = $paiementBusiness;
    }

    function mesInfos($sapeurId)
    {
        // TODO
        // return Ecriture::where('decompte_id', '=', $sapeurId)->get();
    }

    function mesExercices($sapeurId, $exerciceComptableId)
    {
        return ExerciceSapeur::where('sapeur_id', '=', $sapeurId)
            ->join('exercices', 'exercices.id',  '=', 'exercice_sapeur.exercice_id')
            ->where('exercices.exercice_comptable_id', '=', $exerciceComptableId)
            ->get()->toArray();
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
