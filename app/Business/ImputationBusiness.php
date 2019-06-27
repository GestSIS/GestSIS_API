<?php


namespace App\Business;

use App\Contracts\EcritureRepository;
use App\Contracts\ExerciceRepository;
use App\Contracts\IndemniteTypeRepository;
use App\Contracts\InterventionRepository;
use Carbon\Carbon;

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
            $debutNuit = null;
            $finNuit = null;

            if ($indemniteType->taux_nuit !== null) {
                $debutNuit = Carbon::parse($indemniteType->debut);
                $finNuit = Carbon::parse($indemniteType->fin);

                if (!($finNuit > $debutNuit)) {
                    $finNuit->addDays(1);
                }
                $dureeNuit += $debutNuit->floatDiffInHours($finNuit);
            }

            //Durées calculées en heures
            $dureeTarifStandard = 0;
            $dureeTarifWeekend = 0;
            $dureeTarifNuit = 0;

            //TODO Adapt soldeTarif selon la fonction principale
            $soldeTarif = $indemniteType->solde;
            $tauxWeekend = $indemniteType->taux_weekend;
            $tauxNuit = $indemniteType->taux_nuit;

            $testWeekend = $tauxWeekend !== null;
            $testNuit = $tauxNuit !== null;

            foreach ($sapeur as $presence) {
                $debut = Carbon::parse($presence->debut);
                $fin = Carbon::parse($presence->fin);
                $duree = $debut->floatDiffInHours($fin);

                if (!$testWeekend && !$testNuit) {
                    //Pas de taux
                    $dureeTarifStandard += $duree->days * 24 + $duree->h + $duree->m / 60;
                } else {

                    //Arrondir debut à la fin de la première journée
                    //Arrondir fin à la fin de l'avant dernière journée
                    $debutCarbon = $debut->copy()->floorDay();
                    $finCarbon = $debut->copy()->ceilDay();

                    $nbWeekend = 0;
                    $nbWeek = 0;
                    if ($debutCarbon->copy()->addDay(1) < $finCarbon) {
                        $nbWeekend += $debutCarbon->diffInDaysFiltered(function (Carbon $date) {
                            return $date->isWeekend();
                        }, $finCarbon);
                        $nbWeek += $debutCarbon->diffInDaysFiltered(function (Carbon $date) {
                            return !$date->isWeekend();
                        }, $finCarbon);
                    }

                    //Dispatch full days to hours
                    if ($testWeekend && $testNuit) {
                        $dureeTarifStandard = $nbWeek * $dureeNuit;
                        $dureeTarifWeekend = $nbWeekend * 24;
                        $dureeTarifNuit = $nbWeek * (24 - $dureeNuit);
                    } elseif ($testWeekend) {
                        $dureeTarifWeekend = $nbWeekend * 24;
                        $dureeTarifNuit = $nbWeek * 24;
                    } elseif ($testNuit) {
                        $dureeTarifStandard = ($nbWeek + $nbWeekend) * $dureeNuit;
                        $dureeTarifNuit = ($nbWeek + $nbWeekend) * (24 - $dureeNuit);
                    } else {
                        $dureeTarifStandard = ($nbWeek + $nbWeekend) * 24;
                    }

                    //Définition des deux périodes de nuit qui peuvent potentiellement overlap sur la présence
                    $nightPeriodOneStart = $debutCarbon->copy()->addDay($debutCarbon->diffInDays($debutNuit, false));
                    $nightPeriodOneEnd = $debutCarbon->copy()->addDay($debutCarbon->diffInDays($debutNuit, false));
                    $nightPeriodTwoStart = $nightPeriodOneStart->copy()->subDay(1);
                    $nightPeriodTwoEnd = $nightPeriodOneEnd->copy()->subDay(1);

                    //Alternative 1
                    if ($debutCarbon->roundDay()->copy()->addDay(1) === $finCarbon->roundDay()) {
                        //Debut et fin la même journée
                        if ($debutCarbon->isWeekend() && $testWeekend) {
                            $dureeTarifWeekend += $duree;
                        } elseif ($testNuit) {
                            $overlapping = 0;
                            $overlapping += min($debut->max($nightPeriodOneStart)->floatDiffInHours($fin->min($nightPeriodOneEnd)), 0);
                            $overlapping += min($debut->max($nightPeriodTwoStart)->floatDiffInHours($fin->min($nightPeriodTwoEnd)), 0);

                            $dureeTarifNuit += $overlapping;
                            $dureeTarifStandard += $duree - $overlapping;
                        } else {
                            $dureeTarifStandard += $duree;
                        }

                    } else {
                        //Premier jour de la présence -> début
                        if ($debutCarbon->isWeekend() && $testWeekend) {
                            $dureeTarifWeekend += $duree;
                        } elseif ($testNuit) {
                            $overlapping = 0;

                            //Create period 1 start and end date
                            $finJour = $debut->copy()->ceilDay();
                            $overlapping += min($debut->max($nightPeriodOneStart)->floatDiffInHours($finJour->min($nightPeriodOneEnd)), 0);
                            $overlapping += min($debut->max($nightPeriodTwoStart)->floatDiffInHours($finJour->min($nightPeriodTwoEnd)), 0);

                            //Modification de la durée
                            $duree = $debut->floatDiffInHours($finJour);
                            $dureeTarifNuit += $overlapping;
                            $dureeTarifStandard += $duree - $overlapping;
                        } else {
                            $dureeTarifStandard += $duree;
                        }

                        //Deuxième jour de la présence -> fin
                        if ($debutCarbon->isWeekend() && $testWeekend) {
                            $dureeTarifWeekend += $duree;
                        } elseif ($testNuit) {
                            $overlapping = 0;

                            //Adaptation du debut de la journée
                            $debutJour = $fin->copy()->floorDay();

                            $nightPeriodOneStart = $debutCarbon->copy()->addDay($nightPeriodOneStart->diffInDays($debutJour, false));
                            $nightPeriodOneEnd = $debutCarbon->copy()->addDay($nightPeriodOneStart->diffInDays($debutJour, false));
                            $nightPeriodTwoStart = $nightPeriodOneStart->copy()->subDay(1);
                            $nightPeriodTwoEnd = $nightPeriodOneEnd->copy()->subDay(1);

                            $overlapping += min($debutJour->max($nightPeriodOneStart)->floatDiffInHours($fin->min($nightPeriodOneEnd)), 0);
                            $overlapping += min($debutJour->max($nightPeriodTwoStart)->floatDiffInHours($fin->min($nightPeriodTwoEnd)), 0);

                            //Modification de la durée
                            $duree = $debutJour->floatDiffInHours($fin);
                            $dureeTarifNuit += $overlapping;
                            $dureeTarifStandard += $duree - $overlapping;
                        } else {
                            $dureeTarifStandard += $duree;
                        }
                    }
                }
            }//End boucle presences d'un sapeur


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

    private function dureeOverlay($periode1, $periode2)
    {

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
