<?php


namespace App\Business;

use App\Contracts\EcritureRepository;
use App\Contracts\ExerciceRepository;
use App\Contracts\InterventionRepository;
use App\Contracts\IndemniteTypeRepository;

class ImputationBusiness
{
    protected $ecritureRepo;
    protected $indemniteRepo;
    protected $exerciceRepo;
    protected $interventionRepo;

    public function __construct(
        EcritureRepository $ecriture,
        ExerciceRepository $exercice,
        InterventionRepository $intervention,
        IndemniteTypeRepository $indemnite)
    {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->interventionRepo = $intervention;
        $this->indemniteRepo = $indemnite;
    }

    public function imputerExercice($exerciceId, $data)
    {
        $indemniteType = $this->indemniteRepo->findIndemniteExerciceTypeById($data['indemnite_exercice_type_id']);
        $exercice = $this->exerciceRepo->findWithSapeurs($exerciceId);

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
        //TODO Changer statut

    }

    private function isWeekend($date)
    {
        return (date_create('N', strtotime($date)) >= 6);
    }

    public function imputerIntervention($interventionId, $data)
    {
        $indemniteType = $this->indemniteRepo->findIndemniteInterventionTypeById($data['indemnite_intervention_type_id']);
        $intervention = $this->interventionRepo->findWith($interventionId, ['sapeurs', 'phases']);

        $unite = $indemniteType->type_unite_id;
        $designation = $intervention->lieu;

        //Grouper les présences par sapeurs
        $sapeurs = [];
        foreach ($intervention->sapeurs as $presence) {
            if (!array_key_exists($presence->sapeur_id, $sapeurs)) {
                $sapeurs[$presence->sapeur_id] = [];
            }
            array_push($sapeurs[$presence->sapeur_id], $presence);
        }

        $phases = $intervention->phases;

        //TODO Retourne les phases durant cette période
        $getPhases = function ($presence) use ($phases) {

        };

        //TODO Fonction
        foreach ($sapeurs as $sapeur) {
            $dureeNuit = 0;

            if ($indemniteType->taux_nuit !== null) {
                $debutNuit = date_create($indemniteType->debut);
                $finNuit = date_create($indemniteType->fin);

                if (!($finNuit > $debutNuit)) {
                    $finNuit = $finNuit->add(new DateInterval("P1D"));
                }
                $dureeNuit += date_diff($debutNuit, $finNuit);
            }

            //Durées calculées en heures
            $dureeTarifStandard = 0;
            $dureeTarifWeekend = 0;
            $dureeTarifNuit = 0;

            //TODO Adapt soldeTarif selon la fonction principale
            $soldeTarif = $indemniteType->solde;
            $tauxWeekend = $indemniteType->taux_weekend;
            $tauxNuit = $indemniteType->taux_nuit;

            foreach ($sapeur as $presence) {
                $debut = date_create($presence->debut);
                $fin = date_create($presence->fin);
                $duree = date_diff($debut, $fin);

                if ($indemniteType->taux_weekend === null &&
                    $indemniteType->taux_nuit === null) {
                    //Pas de taux
                    $dureeTarifStandard += $duree->days * 24 + $duree->h + $duree->m / 60;
                } elseif ($indemniteType->taux_weekend !== null &&
                    $indemniteType->taux_nuit === null) {
                    //Durée d'une semaine
                    if ($duree->days >= 7) {
                        $quantite = ($duree->days - $duree->days % 7) / 2;
                        $dureeTarifWeekend += 24 * 2 * $quantite;
                        $dureeTarifStandard += 24 * 5 * $quantite;
                    }

                    //TODO Premiers jours

                } elseif ($indemniteType->taux_nuit !== null &&
                    $indemniteType->taux_weekend !== null) {
                    //Durée d'une semaine
                    if ($duree->days >= 7) {
                        $quantite = ($duree->days - $duree->days % 7) / 2;
                        $dureeTarifWeekend += 24 * 2 * $quantite;
                        $dureeTarifStandard += (24 - $dureeNuit) * 5 * $quantite;
                        $dureeTarifNuit += $dureeNuit * 5 * $quantite;
                    }

                    // TODO Tarif nuit & Tarif week-end

                } elseif ($indemniteType->taux_nuit !== null) {
                    //Tarif nuit only

                    //Full days
                    $dureeTarifNuit += $duree->days * $dureeNuit;
                    $dureeTarifStandard += $duree->days * (24 - $dureeNuit);

                    //Remaining <24hours
                    $debut = new DateTime($presence->debut);
                    $fin = $debut->add();
                    $duree = date_diff($debut, $fin);

                    //TODO Finalise last hours

                }
            }

            $soldeStandard = $soldeTarif * $dureeTarifStandard;
            $soldeNuit = $dureeTarifNuit;
            $soldeWeekend = $dureeTarifWeekend;

            //Application des taux
            if ($indemniteType->taux_weekend !== null) {
                $soldeWeekend *= $tauxWeekend;
            }
            if ($indemniteType->taux_nuit !== null) {
                $soldeNuit *= $tauxNuit;
            }

            //Génération des écritures
            if ($soldeStandard > 0) {
                $ecriture = array(
                    'solde' => $soldeStandard,
                    'indemnite' => 0,
                    'frais' => 0,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'designation' => $designation,
                    'total' => $soldeStandard,
                    'tarif' => $soldeTarif,
                    'quantite' => 1,
                    'solde_min' => null,
                    'solde_min_pour' => null,
                    'taux' => null,
                    'sapeur_id' => $sapeur->sapeur_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $intervention->id
                );

                $this->ecritureRepo->persisteNewEcriture($ecriture);
            }

            if ($indemniteType->taux_weekend === $indemniteType->taux_nuit) {
                //TODO Un seul taux spécial
            }

            if ($soldeNuit > 0) {
                $ecriture = array(
                    'solde' => $soldeNuit,
                    'indemnite' => 0,
                    'frais' => 0,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'designation' => $designation + " - Nuit",
                    'total' => $soldeNuit,
                    'tarif' => $soldeTarif,
                    'quantite' => 1,
                    'solde_min' => null,
                    'solde_min_pour' => null,
                    'taux' => null,
                    'sapeur_id' => $sapeur->sapeur_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $intervention->id
                );

                $this->ecritureRepo->persisteNewEcriture($ecriture);
            }

            if ($soldeWeekend > 0) {
                $ecriture = array(
                    'solde' => $soldeWeekend,
                    'indemnite' => 0,
                    'frais' => 0,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'designation' => $designation + " - Weekend",
                    'total' => $soldeWeekend,
                    'tarif' => $soldeTarif,
                    'quantite' => 1,
                    'solde_min' => null,
                    'solde_min_pour' => null,
                    'taux' => null,
                    'sapeur_id' => $sapeur->sapeur_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $intervention->id
                );

                $this->ecritureRepo->persisteNewEcriture($ecriture);
            }
        }

        // TODO Changer le status de l'intervention
    }

    private function imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation)
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

            $this->ecritureRepo->persisteNewEcriture($ecriture);
        }
    }

    private function imputerExerciceParHeureEtFonction($exercice, $sapeurs, $indemniteType, $designation)
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

            $this->ecritureRepo->persisteNewEcriture($ecriture);
        }
    }

    private function imputerExerciceParHeureEtSoldeMin($exercice, $sapeurs, $indemniteType, $designation)
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

            $this->ecritureRepo->persisteNewEcriture($ecriture);
        }
    }
}
