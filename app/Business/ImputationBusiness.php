<?php


namespace App\Business;

use App\Contracts\EcritureRepository;

class ImputationBusiness
{
    protected $repository;

    public function __construct(EcritureRepository $repository)
    {
        $this->repository = $repository;
    }

    public function imputerExercice($exercice, $indemniteType)
    {
        $unite = $indemniteType->type_unite_id;
        $designation = $exercice->designation;
        $sapeurs = array_filter($exercice->sapeurs, function ($sap) {
            return $sap->present;
        });
        if ($unite === 2) {
            $this->imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation);
        } elseif ($unite === 1 && $indemniteType->par_fonction) {
            $this->imputerExerciceParHeureEtFonction($exercice, $sapeurs, $indemniteType, $designation);
        } elseif ($unite === 1 && !$indemniteType->par_fonction) {
            $this->imputerExerciceParHeureEtSoldeMin($exercice, $sapeurs, $indemniteType, $designation);
        } else {
            dd("ERROR");
            //TODO WARNING IN LOGS
        }

// Changer le status de l'exercice

    }

    private
    function imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation)
    {
        // TODO : solde_min should be null

        // Générer écritures
        foreach ($sapeurs as $sapeur) {
            $id = $sapeur->id;
            $fonction_tarif = array_filter($indemniteType->fonctions, function ($f) use ($id) {
                return $f->fonction_id === $id;
            });

            $solde = 0;
            $indemnite = 0;
            if (count($fonction_tarif) > 0) {
                $solde += $fonction_tarif[0]->solde;
                $indemnite += $fonction_tarif[0]->indemnite;
            } else {
                $solde += $indemniteType->solde;
                $indemnite += $indemniteType->indemnite;
            }

            //Par pièce et pas par fonction -> pas de calcul
            $ecriture = array(
                'solde' => $solde,
                'indemnite' => $indemnite,
                'frais' => 0,
                'type_unite_id' => $indemniteType->type_unite_id,
                'designation' => $designation,
                'total' => $solde + $indemnite,
                'tarif' => $solde + $indemnite,
                'quantite' => 1,
                'solde_min' => null,
                'solde_min_pour' => null,
                'taux' => null,
                'sapeur_id' => $sapeur->sapeur_id,
                'exercice_comptable_id' => $exercice->exercice_comptable_id,
                'exercice_id' => $exercice->id
            );

            $this->repository->create($ecriture);
        }
    }

    private
    function imputerExerciceParHeureEtFonction($exercice, $sapeurs, $indemniteType, $designation)
    {
        //TODO solde_min should be null
        //En minutes
        $duree = $exercice->duree / 60;

        // Générer écritures
        foreach ($sapeurs as $sapeur) {
            $id = $sapeur->id;
            $fonction_tarif = array_filter($indemniteType->fonctions, function ($f) use ($id) {
                return $f->fonction_id === $id;
            });

            $soldeTarif = 0;
            $indemniteTarif = 0;
            if (count($fonction_tarif) > 0) {
                $soldeTarif += $fonction_tarif[0]->solde;
                $indemniteTarif += $fonction_tarif[0]->indemnite;
            } else {
                $soldeTarif += $indemniteType->solde;
                $indemniteTarif += $indemniteType->indemnite;
            }

            $solde = $soldeTarif * $duree;
            $indemnite = $indemniteTarif * $duree;

            //Par heure -> calcul de la durée
            $ecriture = array(
                'solde' => $solde,
                'indemnite' => $indemnite,
                'frais' => 0,
                'type_unite_id' => $indemniteType->type_unite_id,
                'designation' => $designation,
                'total' => $solde + $indemnite,
                'tarif' => $soldeTarif + $indemniteTarif,
                'quantite' => $exercice->duree / 60,
                'solde_min' => $indemniteType->solde_min,
                'solde_min_pour' => $indemniteType->solde_min_pour,
                'taux' => null,
                'sapeur_id' => $sapeur->sapeur_id,
                'exercice_comptable_id' => $exercice->exercice_comptable_id,
                'exercice_id' => $exercice->id
            );

            $this->repository->create($ecriture);
        }
    }

    private
    function imputerExerciceParHeureEtSoldeMin($exercice, $sapeurs, $indemniteType, $designation)
    {
        //TODO indemnite should be null
        //En minutes
        $duree = $exercice->duree / 60;

        // Générer écritures
        foreach ($sapeurs as $sapeur) {
            $solde = 0;
            if ($duree > $indemniteType->solde_min_pour) {
                $solde += $indemniteType->solde_min;
                $duree -= $indemniteType->solde_min_pour;
            } else {
                $solde += $indemniteType->solde_min * $duree;
                $duree = 0;
            }

            $solde += $indemniteType->solde * $duree;

            //Par heure -> calcul de la durée
            $ecriture = array(
                'solde' => $solde,
                'indemnite' => 0,
                'frais' => 0,
                'type_unite_id' => $indemniteType->type_unite_id,
                'designation' => $designation,
                'total' => $solde,
                'tarif' => $indemniteType->solde,
                'quantite' => $exercice->duree / 60,
                'solde_min' => $indemniteType->solde_min,
                'solde_min_pour' => $indemniteType->solde_min_pour,
                'taux' => null,
                'sapeur_id' => $sapeur->sapeur_id,
                'exercice_comptable_id' => $exercice->exercice_comptable_id,
                'exercice_id' => $exercice->id
            );

            $this->repository->create($ecriture);
        }
    }
}
