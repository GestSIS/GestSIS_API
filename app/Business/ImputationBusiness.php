<?php


namespace App\Business;

use App\Contracts\EcritureRepository;
use App\Contracts\ExerciceRepository;
use App\Contracts\FraisTypeRepository;
use App\Contracts\IndemniteTypeRepository;
use App\Contracts\InterventionRepository;
use App\Contracts\SapeurRepository;
use Carbon\Carbon;

class ImputationBusiness
{
    protected $ecritureRepo;
    protected $indemniteRepo;
    protected $fraisRepo;
    protected $exerciceRepo;
    protected $interventionRepo;

    public function __construct(
        EcritureRepository $ecriture,
        SapeurRepository $sapeur,
        ExerciceRepository $exercice,
        InterventionRepository $intervention,
        IndemniteTypeRepository $indemnite,
        FraisTypeRepository $frais)
    {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->sapeurRepo = $sapeur;
        $this->interventionRepo = $intervention;
        $this->indemniteRepo = $indemnite;
        $this->fraisRepo = $frais;
    }

    public function imputerAnnuel(int $exerciceComptableId)
    {
        // TODO Check pas déjà imputée cette année

        $indemnites = $this->indemniteRepo->listeIndemniteAnnuelType();
        $frais = $this->fraisRepo->listeFraisAnnuelType();

        $sapeurs = array_filter($this->sapeurRepo->listeSapeurLight(),
            function ($s) {
                return $s->actif;
            }
        );

        //Génération des indemnités annuels
        foreach ($indemnites as $i) {
            array_map(
                function ($s) use ($i, $exerciceComptableId) {
                    $this->imputerIndemniteSapeur($i, $s, $exerciceComptableId);
                },
                array_filter($sapeurs, function ($s) use ($i) {
                    return $i->fonction_id === $s->fonction_id;
                })
            );
        }

        //Générations des frais annuels
        foreach ($frais as $f) {
            array_map(
                function ($s) use ($f, $exerciceComptableId) {
                    $this->imputerFraisSapeur($f, $s, $exerciceComptableId);
                },
                array_filter($sapeurs, function ($s) use ($f) {
                    return $f->fonction_id === $s->fonction_id;
                })
            );
        }
    }

    private function imputerIndemniteSapeur($indemniteType, $sapeur, $exerciceComptableId)
    {
        $total = $indemniteType->montant * $indemniteType->quantite;
        $ecriture = array(
            'solde' => 0,
            'indemnite' => $indemniteType->montant,
            'frais' => 0,
            'type_unite_id' => 1,
            'designation' => $indemniteType->designation,
            'total' => $total,
            'tarif' => $indemniteType->montant,
            'quantite' => $indemniteType->quantite,
            'solde_min' => null,
            'solde_min_pour' => null,
            'taux' => null,
            'sapeur_id' => $sapeur->id,
            'exercice_comptable_id' => $exerciceComptableId,
            'indemnite_annuel_type_id' => $indemniteType->id
        );

        $this->ecritureRepo->persisteNewEcriture($ecriture);
    }

    private function imputerFraisSapeur($fraisType, $sapeur, $exerciceComptableId)
    {
        $total = $fraisType->montant * $fraisType->quantite;
        $ecriture = array(
            'solde' => 0,
            'indemnite' => 0,
            'frais' => $fraisType->montant,
            'type_unite_id' => 1,
            'designation' => $fraisType->designation,
            'total' => $total,
            'tarif' => $fraisType->montant,
            'quantite' => $fraisType->quantite,
            'solde_min' => null,
            'solde_min_pour' => null,
            'taux' => null,
            'sapeur_id' => $sapeur->id,
            'exercice_comptable_id' => $exerciceComptableId,
            'frais_annuel_type_id' => $fraisType->id
        );

        $this->ecritureRepo->persisteNewEcriture($ecriture);
    }

    public function imputerExercice($exerciceId, $data)
    {
        $exercice = $this->exerciceRepo->getExerciceWithSapeurById($exerciceId);

        if ($exercice->statut !== ExerciceBusiness::EXERCICE_STATUT_VALIDE) {
            throw new ArrayValidatorException(array("message" => "Impossible d'imputre cet exercice"));
        }

        $indemniteType = $this->indemniteRepo->findIndemniteExerciceTypeById($data['indemnite_exercice_type_id']);

        $unite = $indemniteType->type_unite_id;
        $designation = $exercice->designation;
        $sapeurs = array_filter($exercice->sapeurs, function ($sap) {
            return $sap->present;
        });
        if ($unite === 2) {
            $this->imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation);
        } elseif ($unite === 1 && $indemniteType->par_fonction) {
            $this->imputerExerciceParHeureEtFonction($exercice, $sapeurs, $indemniteType, $designation);
        } elseif ($unite === 1 && $indemniteType->solde_min !== null) {
            $this->imputerExerciceParHeureEtSoldeMin($exercice, $sapeurs, $indemniteType, $designation);
        } else {
            dd($indemniteType);
            dd("ERROR");
            return false;
            //TODO WARNING IN LOGS
        }

        //TODO Ajout date imputation

        // Changer le statut de l'exercice
        return $this->exerciceRepo->updateExerciceById($exerciceId, ["statut" => ExerciceBusiness::EXERCICE_STATUT_IMPUTE])->statut;
    }

    public function imputerIntervention($interventionId, $data)
    {
        $indemniteType = $this->indemniteRepo->findIndemniteInterventionTypeById($data['indemnite_intervention_type_id']);
        $intervention = $this->interventionRepo->findWith($interventionId, ['presences', 'phases']);

        if ($intervention->statut !== InterventionBusiness::INTERVENTION_STATUT_VALIDE) {
            throw new ArrayValidatorException(array("message" => "Impossible d'imputer cette intervention"));
        }

        $unite = $indemniteType->type_unite_id;
        $designation = $intervention->lieu;

        //Grouper les présences par sapeurs
        $sapeurs = [];
        foreach ($intervention->presences as $presence) {
            if (!array_key_exists($presence->sapeur_id, $sapeurs)) {
                $sapeurs[$presence->sapeur_id] = [];
            }
            array_push($sapeurs[$presence->sapeur_id], $presence);
        }

        $phases = $intervention->phases;

        //TODO Retourne les phases durant cette période
        $getPhases = function ($presence) use ($phases) {

        };

        foreach ($sapeurs as $sapeur_id => $presences) {
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

            $trace = "";

            foreach ($presences as $presence) {
                $debut = Carbon::parse($presence->debut);
                $fin = Carbon::parse($presence->fin);
                $duree = $debut->floatDiffInHours($fin);

                if (!$testWeekend && !$testNuit) {
                    //Pas de taux
                    $dureeTarifStandard += $duree;
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
                        $dureeTarifStandard += $nbWeek * $dureeNuit;
                        $dureeTarifWeekend += $nbWeekend * 24;
                        $dureeTarifNuit += $nbWeek * (24 - $dureeNuit);
                    } elseif ($testWeekend) {
                        $dureeTarifWeekend += $nbWeekend * 24;
                        $dureeTarifNuit += $nbWeek * 24;
                    } elseif ($testNuit) {
                        $dureeTarifStandard += ($nbWeek + $nbWeekend) * $dureeNuit;
                        $dureeTarifNuit += ($nbWeek + $nbWeekend) * (24 - $dureeNuit);
                    } else {
                        $dureeTarifStandard += ($nbWeek + $nbWeekend) * 24;
                    }

                    //Définition des deux périodes de nuit qui peuvent potentiellement overlap sur la présence
                    $diff = $debutNuit->diffInDays($debutCarbon, false);
                    $nightPeriodOneStart = $debutNuit->copy()->addDays($diff);
                    $nightPeriodOneEnd = $finNuit->copy()->addDays($diff);
                    $nightPeriodTwoStart = $nightPeriodOneStart->copy()->subDay();
                    $nightPeriodTwoEnd = $nightPeriodOneEnd->copy()->subDay();

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
                        //Modification de la durée
                        $finJour = $debut->copy()->ceilDay();
                        $duree = $debut->floatDiffInHours($finJour);

                        //Premier jour de la présence -> début
                        if ($debutCarbon->isWeekend() && $testWeekend) {
                            $dureeTarifWeekend += $duree;
                        } elseif ($testNuit) {
                            $overlapping = 0;

                            //Create period 1 start and end date
                            $overlapping += max($debut->max($nightPeriodOneStart)->floatDiffInHours($finJour->min($nightPeriodOneEnd), false), 0);
                            $overlapping += max($debut->max($nightPeriodTwoStart)->floatDiffInHours($finJour->min($nightPeriodTwoEnd), false), 0);

                            $dureeTarifNuit += $overlapping;
                            $dureeTarifStandard += $duree - $overlapping;
                        } else {
                            $dureeTarifStandard += $duree;
                        }

                        //Deuxième jour de la présence -> fin

                        //Modification de la durée
                        $debutJour = $fin->copy()->floorDay();
                        $duree = $debutJour->floatDiffInHours($fin);

                        if ($debutCarbon->isWeekend() && $testWeekend) {
                            $dureeTarifWeekend += $duree;
                        } elseif ($testNuit) {
                            $overlapping = 0;

                            $nightPeriodOneStart = $nightPeriodOneStart->copy()->addDays($nightPeriodOneStart->diffInDays($debutJour, false));
                            $nightPeriodOneEnd = $nightPeriodOneEnd->copy()->addDays($nightPeriodOneStart->diffInDays($debutJour, false));
                            $nightPeriodTwoStart = $nightPeriodOneStart->copy()->subDay(1);
                            $nightPeriodTwoEnd = $nightPeriodOneEnd->copy()->subDay(1);

                            $overlapping += max($debutJour->max($nightPeriodOneStart)->floatDiffInHours($fin->min($nightPeriodOneEnd), false), 0);
                            $overlapping += max($debutJour->max($nightPeriodTwoStart)->floatDiffInHours($fin->min($nightPeriodTwoEnd), false), 0);

                            $dureeTarifNuit += $overlapping;
                            $dureeTarifStandard += $duree - $overlapping;
                        } else {
                            $dureeTarifStandard += $duree;
                        }
                    }
                }
            }//End boucle presences d'un sapeur

            $soldeStandard = $soldeTarif * $dureeTarifStandard;
            $soldeNuit = $soldeTarif * $dureeTarifNuit;
            $soldeWeekend = $soldeTarif * $dureeTarifWeekend;


            //Application des taux
            if ($testWeekend) {
                $soldeWeekend *= $tauxWeekend;
            }
            if ($testNuit) {
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
                    'sapeur_id' => $sapeur_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $intervention->id
                );

                $this->ecritureRepo->persisteNewEcriture($ecriture);
            }

            if ($soldeNuit > 0) {
                $ecriture = array(
                    'solde' => $soldeNuit,
                    'indemnite' => 0,
                    'frais' => 0,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'designation' => $designation . " - Nuit",
                    'total' => $soldeNuit,
                    'tarif' => $soldeTarif,
                    'quantite' => 1,
                    'solde_min' => null,
                    'solde_min_pour' => null,
                    'taux' => $tauxNuit,
                    'sapeur_id' => $sapeur_id,
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
                    'designation' => $designation . " - Weekend",
                    'total' => $soldeWeekend,
                    'tarif' => $soldeTarif,
                    'quantite' => 1,
                    'solde_min' => null,
                    'solde_min_pour' => null,
                    'taux' => $tauxWeekend,
                    'sapeur_id' => $sapeur_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $intervention->id
                );

                $this->ecritureRepo->persisteNewEcriture($ecriture);
            }
        }

        //TODO Ajout date imputation

        // Update statut
        return $this->interventionRepo->editInterventionInformationsById($interventionId, [
            "statut" => InterventionBusiness::INTERVENTION_STATUT_IMPUTE
        ])->statut;
    }

    private function imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation)
    {
        // TODO : solde_min should be null

        // Générer écritures
        foreach ($sapeurs as $sapeur) {
            $id = $this->sapeurRepo->getSapeurDetailsById($sapeur->sapeur_id)->fonction_id;

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
            $id = $this->sapeurRepo->getSapeurDetailsById($sapeur->sapeur_id)->fonction_id;

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
        //TODO indemnite et par fonction should be null
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
