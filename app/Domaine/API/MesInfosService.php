<?php


namespace App\Domaine\API;

use App\Domaine\Business\PaiementBusiness;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\Paiement;

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

    function mesExercices($sapeurId)
    {
        return Exercice::whereIn('id', function ($query) use ($sapeurId) {
            $query->select('exercice_id')
                ->from(with(new ExerciceSapeur())->getTable())
                ->where('sapeur_id', $sapeurId);
        })->get()->toArray();
    }

    function mesDecomptes($sapeurId)
    {
        $paiements = Paiement::where('sapeur_id', '=', $sapeurId)
            ->join('decomptes', 'paiements.decompte_id', '=', 'decomptes.id')
            ->select('paiements.*', 'decomptes.date as date', 'decomptes.designation as decompte')->get();
        $ecritures = Ecriture::where('sapeur_id', '=', $sapeurId)->whereNotNull('decompte_id')->get();

        return [
            'paiements' => $paiements,
            'ecritures' => $ecritures,
        ];
    }

    function imprimerMonDecompte($sapeurId)
    {
        $paiements = Paiement::where('sapeur_id', '=', $sapeurId)
            ->join('decomptes', 'paiement.decompte_id', '=', 'decomptes.id')
            ->select('paiements.*', 'decomptes.designation as decompte');
        $ecritures = Ecriture::where('sapeur_id', '=', $sapeurId)->whereNotNull('decompte_id')->get();

        return [
            'paiements' => $paiements,
            'ecritures' => $ecritures,
        ];
    }
}
