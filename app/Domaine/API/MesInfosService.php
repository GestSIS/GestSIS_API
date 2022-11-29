<?php


namespace App\Domaine\API;

use App\Domaine\Business\PaiementBusiness;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\HeureExercice;
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
        $heures = HeureExercice::where('sapeur_id', '=', $sapeurId)->get()->toArray();
        $sapeurs = ExerciceSapeur::where('sapeur_id', '=', $sapeurId)->get()->toArray();

        $exercices = Exercice::where('exercice_comptable_id', '=', $exerciceComptableId)
            ->whereIn('id', array_merge(
                array_map(fn ($h) => $h['exercice_id'], $heures),
                array_map(fn ($h) => $h['exercice_id'], $sapeurs),
            ))->get()->toArray();

        $dictionary = [];
        foreach ($exercices as $exercice) {
            $dictionary[$exercice['id']] = $exercice;
            $dictionary[$exercice['id']]['heures'] = [];
            $dictionary[$exercice['id']]['presence'] = null;
        }

        foreach ($sapeurs as $sapeur) {
            if (array_key_exists($sapeur['exercice_id'], $dictionary)) {
                $dictionary[$sapeur['exercice_id']]['presence'] = $sapeur;
            }
        }
        foreach ($heures as $heure) {
            if (array_key_exists($sapeur['exercice_id'], $dictionary)) {
                $dictionary[$heure['exercice_id']]['heures'][] = $heure;
            }
        }
        return array_values($dictionary);
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
