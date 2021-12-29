<?php

namespace App\Domaine\Business;

use App\Domaine\SPI\EcritureRepository;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\SPI\FraisTypeRepository;
use App\Domaine\SPI\IndemniteTypeRepository;
use App\Domaine\SPI\InterventionRepository;
use App\Domaine\SPI\SapeurRepository;
use App\Domaine\Exceptions\ArrayException;
use Carbon\Carbon;
use App\Infrastructure\Models\Amende;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\ExerciceComptable;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\FonctionSapeur;
use App\Infrastructure\Models\HeureExercice;

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
        FraisTypeRepository $frais
    ) {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->sapeurRepo = $sapeur;
        $this->interventionRepo = $intervention;
        $this->indemniteRepo = $indemnite;
        $this->fraisRepo = $frais;
    }

    protected const UNITE_CHF_PAR_PIECE = 1;
    protected const UNITE_CHF_PAR_HEURE = 2;

    public function creerExerciceComptable($data)
    {
        $exerciceComptable = new ExerciceComptable();
        $exerciceComptable->fill($data);
        $exerciceComptable->boucle = 0;
        $exerciceComptable->save();
        return $exerciceComptable;
    }

    /**
     * Générer les amendes pour un sapeur
     */
    public function genererAmendesSapeur($exerciceComptableId, $sapeurId)
    {
        // Chargment de la config des amendes
        $amendes = Amende::orderBy('ordre', 'ASC')->get();
        $nbAmende = count($amendes);

        if ($nbAmende <= 0) {
            throw new ArrayException(['config' => 'Pas de configurations d\'amendes'], "Aucune amende configurée");
        }

        // Chargement des exercices amendés du sapeur
        $exercices = ExerciceSapeur::where([
            ['sapeur_id', '=', $sapeurId],
            ['amende', '=', 1]
        ])->join('exercices', 'exercices.id', '=', 'exercice_sapeur.exercice_id')
            ->where('exercices.exercice_comptable_id', $exerciceComptableId)
            ->orderBy('exercices.date', 'ASC')
            ->orderBy('exercices.heure')->get();

        // Suppression de amendes existantes
        Ecriture::where([
            ['exercice_comptable_id', '=', $exerciceComptableId],
            ['sapeur_id', '=', $sapeurId],
            ['amende', '=', true]
        ])->delete();

        // Pour l'instant juste générer de nouvelles amendes
        $ecritures = [];
        $i = 0;
        foreach ($exercices as $exercice) {

            $amende = $amendes[$i];

            // Creation d'une écriture pour chaque exercice amendé
            $ecriture = array(
                'indemnite' => 0,
                'solde' => 0,
                'frais' => 0,

                'amende' => True,
                'total' => $amende->montant,
                'designation' => $exercice->designation,
                'tarif' => 0,
                'quantite' => 0,

                'sapeur_id' => $exercice->sapeur_id,
                'exercice_id' => $exercice->exercice_id,
                'compte_id' => $amende->compte_id,
                'exercice_comptable_id' => $exerciceComptableId,
                'ecriture_categorie_id' => $amende->ecriture_categorie_id,

                'decompte_id' => null,
                'heure' => null,
                'date' => $exercice->date,
            );

            $ecritures[] = $ecriture;

            if ($i + 1 < $nbAmende) {
                $i++;
            }
        }

        Ecriture::insert($ecritures);
        return $ecritures;
    }

    /**
     * Générer les amendes pour l'année comptable en cours
     */
    public function genererAmendesAnnuels($exerciceComptableId)
    {
        // Chargment de la config des amendes
        $amendes = Amende::orderBy('ordre', 'ASC')->get();
        $nbAmende = count($amendes);

        if ($nbAmende <= 0) {
            throw new ArrayException(['config' => 'Pas de configurations d\'amendes'], "Aucune amende configurée");
        }

        // Chargement des exercices amendés du sapeur
        $exercices = ExerciceSapeur::where([
            ['amende', '=', 1]
        ])->join('exercices', 'exercices.id', '=', 'exercice_sapeur.exercice_id')
            ->where('exercices.exercice_comptable_id', $exerciceComptableId)
            ->orderBy('exercice_sapeur.sapeur_id', 'ASC')
            ->orderBy('exercices.date', 'ASC')
            ->orderBy('exercices.heure')->get();

        // Suppression de amendes existantes
        Ecriture::where([
            ['exercice_comptable_id', '=', $exerciceComptableId],
            ['amende', '=', true]
        ])->delete();

        // Pour l'instant juste générer de nouvelles amendes
        $newEcritures = array();
        $i = 0;
        $sapeurId = -1;

        foreach ($exercices as $exercice) {
            if ($sapeurId != $exercice->sapeur_id) {
                $i = 0;
                $sapeurId = $exercice->sapeur_id;
            }

            $amende = $amendes[$i];

            // Creation d'une écriture pour chaque exercice amendé
            $ecriture = array(
                'solde' => 0,
                'solde_min' => null,
                'solde_min_pour' => null,
                'indemnite' => 0,
                'taux' => null,
                'taux_description' => null,
                'frais' => 0,
                'amende' => True,
                'type_unite_id' => null,
                'designation' => $exercice->designation,
                'total' => $amende->montant,
                'tarif' => 0,
                'quantite' => 0,
                'sapeur_id' => $exercice->sapeur_id,
                'exercice_id' => $exercice->exercice_id,
                'intervention_id' => null,
                'compte_id' => $amende->compte_id,
                'exercice_comptable_id' => $exerciceComptableId,
                'ecriture_categorie_id' => $amende->ecriture_categorie_id,
                'indemnite_annuel' => False,
                'frais_annuel' => False,
                'decompte_id' => null,
                'heure' => null,
                'date' => $exercice->date,
            );

            array_push($newEcritures, $ecriture);

            if ($i + 1 < $nbAmende) {
                $i++;
            }
        }

        Ecriture::insert($newEcritures);
        return $newEcritures;
    }

    /**
     * Génères des frais annuels pour les sapeurs n'ayant pas encore de frais annuels
     */
    public function imputerAnnuel(int $exerciceComptableId)
    {
        // Choix disponible pour une seule imputation annuelle :
        // 1. ~~ Si déjà une imputation pour l'année alors ne rien faire~~
        // 2. OUI -> Ajouter des imputations uniquement pour les sapeurs qui n'ont pas de frais pour l'instant
        // 3. ~~ Tout supprimer pour l'année courante et tout regénérer~~
        //
        // Notes :
        // - Ne prend actuellement en compte que la fonction actuelle et non pas la date de l'entrée en vigeure de cette fonction
        // - Prend uniquement les sapeurs actifs

        $indemnitesType = $this->indemniteRepo->listeIndemniteAnnuelType();
        $fraisType = $this->fraisRepo->listeFraisAnnuelType();

        // FIXME: regénérer que pour les sapeurs ne possédants pas d'indemnités ???
        $ecritures = $this->ecritureRepo->listeEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);
        Ecriture::where(function ($query) {
            $query->where('frais_annuel', true);
            $query->orWhere('indemnite_annuel', true);
        })->whereNull('decompte_id')->delete();
        // TODO: Que faire avec les indemnités déjà payées ?
        // Ne pas générer les indemnités pour ces sapeurs ?

        // Exercice comptable
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        $debut = $exerciceComptable->debut;
        $fin = $exerciceComptable->fin;

        // Fonction gardée si intersect avec exercice comptable actuel
        $sapeurs = FonctionSapeur::where(function ($query) use ($debut, $fin) {
            $query
                ->where([
                    ['debut', '<=', $debut],
                    ['fin', '>=', $debut],
                ])
                ->orWhere(function ($query) use ($debut) {
                    $query->where('debut', '<=', $debut);
                    $query->whereNull('fin');
                })
                ->orWhere([
                    ['debut', '>=', $debut],
                    ['debut', '<=', $fin],
                ]);
        })
            ->join('fonctions', 'fonctions.id', '=', 'fonction_sapeur.fonction_id')
            ->orderBy('fonctions.tri')
            ->distinct(['sapeur_id', 'fonction_id'])
            ->select(['sapeur_id', 'fonction_id', 'tri'])->get();

        // Group by sapeur_id
        $sapeursGrouped = [];
        foreach ($sapeurs as $sapeur) {
            $sapeursGrouped[$sapeur->sapeur_id][] = $sapeur->fonction_id;
        }

        // Foreach indemnité annuelle
        foreach ($indemnitesType as $type) {
            // Génère le mapping -> ["fonction_id" => 'indemnite'];
            $mapping = array_reduce(array_map(
                fn ($indemnite) => [$indemnite->fonction_id => $indemnite],
                $type->indemniteAnnuels
            ), fn ($a, $b) => $a + $b, []);

            foreach ($sapeursGrouped as $sapeurId => $fonctions) {
                foreach ($fonctions as $fonctionId) {
                    if (array_key_exists($fonctionId, $mapping)) {
                        $indemnite = $mapping[$fonctionId];
                        $this->imputerIndemniteSapeur($type, $indemnite, $sapeurId, $exerciceComptableId);

                        if (!$type->cumulable) {
                            // Non-cumulable, on passe au sapeur suivant
                            break;
                        }
                    }
                }
            }
        }

        // Foreach frais annuel
        foreach ($fraisType as $type) {
            // Génére le mapping -> ["fonction_id" => 'indemnite'];
            $mapping = array_reduce(array_map(
                fn ($frais) => [$frais->fonction_id => $frais],
                $type->fraisAnnuels
            ), fn ($a, $b) => $a + $b, []);

            foreach ($sapeursGrouped as $sapeurId => $fonctions) {
                foreach ($fonctions as $fonctionId) {
                    if (array_key_exists($fonctionId, $mapping)) {
                        $frais = $mapping[$fonctionId];
                        $this->imputerFraisSapeur($type, $frais, $sapeurId, $exerciceComptableId);

                        if (!$type->cumulable) {
                            // Non-cumulable, on passe au sapeur suivant
                            break;
                        }
                    }
                }
            }
        }
    }

    private function imputerIndemniteSapeur($indemniteType, $indemnite, $sapeurId, $exerciceComptableId)
    {
        $total = $indemnite->montant * $indemnite->quantite;
        $ecriture = array(
            'solde' => 0,
            'indemnite' => $indemnite->montant,
            'frais' => 0,
            'type_unite_id' => $indemnite->type_unite_id,
            'designation' => $indemniteType->designation,
            'total' => $total,
            'tarif' => $indemnite->montant,
            'quantite' => $indemnite->quantite,
            'sapeur_id' => $sapeurId,
            'compte_id' => $indemniteType->compte_id,
            'exercice_comptable_id' => $exerciceComptableId,
            'indemnite_annuel' => true,
            'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id
        );

        $this->ecritureRepo->persisteNewEcriture($ecriture);
    }

    private function imputerFraisSapeur($fraisType, $frais, $sapeurId, $exerciceComptableId)
    {
        $total = $frais->montant * $frais->quantite;
        $ecriture = array(
            'solde' => 0,
            'indemnite' => 0,
            'frais' => $frais->montant,
            'type_unite_id' => $frais->type_unite_id,
            'designation' => $fraisType->designation,
            'total' => $total,
            'tarif' => $frais->montant,
            'quantite' => $frais->quantite,
            'sapeur_id' => $sapeurId,
            'compte_id' => $fraisType->compte_id,
            'exercice_comptable_id' => $exerciceComptableId,
            'frais_annuel' => true,
            'ecriture_categorie_id' => $fraisType->ecriture_categorie_id,
        );

        $this->ecritureRepo->persisteNewEcriture($ecriture);
    }

    /**
     * Générer les écritures liés aux présences des sapeurs durant cette intervention
     * 
     * Décompose le temps de chaque sapeurs entre
     * - Week-end (nuit inclus)
     * - Nuit
     * - Normal
     * Puis calcul le total avec les taux paramétrés.
     */
    public function imputerIntervention($interventionId, $data)
    {
        $indemniteType = $this->indemniteRepo->findIndemniteInterventionTypeById($data['indemnite_intervention_type_id']);
        $intervention = $this->interventionRepo->findWith($interventionId, ['presences', 'phases', 'localite', 'typeIntervention']);

        if ($intervention->statut !== InterventionBusiness::INTERVENTION_STATUT_VALIDE) {
            throw new ArrayException(array("message" => "Impossible d'imputer cette intervention"));
        }

        if ($indemniteType->taux_weekend > 0 || $indemniteType->taux_nuit > 0) {
            $this->imputerInterventionTaux($interventionId, $intervention, $indemniteType, $data);
        } else {
            $this->imputerInterventionSoldeMin($interventionId, $intervention, $indemniteType, $data);
        }

        // Update statut
        return $this->interventionRepo->editInterventionInformationsById($interventionId, [
            "statut" => InterventionBusiness::INTERVENTION_STATUT_IMPUTE
        ])->statut;
    }

    private function imputerInterventionSoldeMin($interventionId, $intervention, $indemniteType, $data)
    {
        $unite = $indemniteType->type_unite_id;

        // Grouper les présences par sapeurs
        $sapeurs = [];
        foreach ($intervention->presences as $presence) {
            if (!array_key_exists($presence->sapeur_id, $sapeurs)) {
                $sapeurs[$presence->sapeur_id] = [];
            }
            array_push($sapeurs[$presence->sapeur_id], $presence);
        }

        $phases = collect($intervention->phases)->sortByDesc('debut');

        $soldeMinDuree = array();
        $nonSoldeMinDuree = array();

        $indemnite_phase_id = $indemniteType->phase_id;

        // Sépare les période de chaque sapeur entre les différentes phases
        foreach ($sapeurs as $sapeurId => $presences) {
            $soldeMinDureeSapeur = 0;
            $nonSoldeMinDureeSapeur = 0;
            foreach ($presences as $periode) {
                $debut = Carbon::parse($periode->debut);
                $fin = Carbon::parse($periode->fin);
                foreach ($phases as $phase) {
                    $phaseDebut = Carbon::parse($phase->debut);

                    if ($phase->debut != NULL && $phaseDebut->gte($fin)) {
                        continue;
                    }

                    if ($phase->debut == NULL) {
                        $phaseDebut = $debut;
                    }

                    $temp = $phaseDebut->max($debut);
                    $duree = $temp->diffInMinutes($fin) / 60;
                    $fin = $temp;

                    // Totalité des périodes restantes pour cette phase
                    if ($indemnite_phase_id == NULL || $indemnite_phase_id == 0 || $phase->phase_type_id == $indemnite_phase_id) {
                        $soldeMinDureeSapeur += $duree;
                    } else {
                        $nonSoldeMinDureeSapeur += $duree;
                    }
                    break;
                }
            }
            $soldeMinDuree[$sapeurId] = ($soldeMinDuree[$sapeurId] ?? 0) + $soldeMinDureeSapeur;
            $nonSoldeMinDuree[$sapeurId] = ($nonSoldeMinDuree[$sapeurId] ?? 0) + $nonSoldeMinDureeSapeur;
        }

        $solde = $indemniteType->solde;
        $soldeMin = $indemniteType->solde_min ?? $indemniteType->solde;
        $soldeMinPour = $indemniteType->solde_min ?? 1;
        $designation = "{$intervention->localite->designation} ({$intervention->type->designation}) $intervention->lieu";

        $ecritures = array();
        foreach ($soldeMinDuree as $sapeurId => $duree) {
            $nonDuree = $nonSoldeMinDuree[$sapeurId];

            $total = 0;

            if ($duree > $soldeMinPour) {
                $total += $soldeMin;
                $duree -= $soldeMinPour;
            }

            $total += $indemniteType->solde * $duree;
            $total += $indemniteType->solde * $nonDuree;

            if ($duree > 0) {
                $ecritures[] = array(
                    'solde' => $total,
                    'indemnite' => 0,
                    'frais' => 0,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'designation' => $designation,
                    'total' => $total,
                    'tarif' => $solde,
                    'quantite' => $duree + $nonDuree,
                    'solde_min' => null,
                    'solde_min_pour' => null,
                    'sapeur_id' => $sapeurId,
                    'compte_id' => $indemniteType->compte_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $intervention->id,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                    'date' => $intervention->date_debut,
                    'heure' => $intervention->heure_debut,
                );
            }
            if ($nonDuree) {
                $ecritures[] = array(
                    'solde' => $total,
                    'indemnite' => 0,
                    'frais' => 0,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'designation' => $designation,
                    'total' => $total,
                    'tarif' => $solde,
                    'quantite' => $nonDuree,
                    'solde_min' => null,
                    'solde_min_pour' => null,
                    'sapeur_id' => $sapeurId,
                    'compte_id' => $indemniteType->compte_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $intervention->id,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                    'date' => $intervention->date_debut,
                    'heure' => $intervention->heure_debut,
                );
            }
        }

        Ecriture::insert($ecritures);
    }

    private function imputerInterventionTaux($interventionId, $intervention, $indemniteType, $data)
    {
        // $dateImputation = $data['date_imputation']; // TODO: Ajouter date d'imputation ?
        $designation = "{$intervention->localite->designation} ({$intervention->type->designation}) $intervention->lieu";

        // Grouper les présences par sapeurs
        $sapeurs = [];
        foreach ($intervention->presences as $presence) {
            if (!array_key_exists($presence->sapeur_id, $sapeurs)) {
                $sapeurs[$presence->sapeur_id] = [];
            }
            array_push($sapeurs[$presence->sapeur_id], $presence);
        }

        // $phases = $intervention->phases;

        // //TODO: Retourne les phases durant cette période
        // $getPhases = function ($presence) use ($phases) {
        // };
        $ecritures = array();

        // Calcul la durée de présence dans chaque catégorie (week-end, nuit, standard)
        foreach ($sapeurs as $sapeur_id => $presences) {
            // Durées calculées en heures
            $dureeTarifStandard = 0;
            $dureeTarifWeekend = 0;
            $dureeTarifNuit = 0;

            // Durée de la période de nuit
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

            // Récupération des tarifs
            $soldeTarif = $indemniteType->solde;
            $tauxWeekend = $indemniteType->taux_weekend;
            $tauxNuit = $indemniteType->taux_nuit;

            $testWeekend = $tauxWeekend !== null;
            $testNuit = $tauxNuit !== null;

            foreach ($presences as $presence) {
                $debut = Carbon::parse($presence->debut);
                $fin = Carbon::parse($presence->fin);
                $duree = $debut->floatDiffInHours($fin);

                if (!$testWeekend && !$testNuit) {
                    //Pas de taux
                    $dureeTarifStandard += $duree;
                } else {

                    // Arrondir debut à la fin de la première journée
                    // Arrondir fin à la fin de l'avant dernière journée
                    $debutCarbon = $debut->copy()->ceilDay();
                    $finCarbon = $fin->copy()->floorDay();

                    $nbJourWeekend = 0;
                    $nbJourSemaine = 0;
                    if ($debutCarbon < $finCarbon) {
                        $nbJourWeekend += $debutCarbon->diffInDaysFiltered(function (Carbon $date) {
                            return $date->isWeekend();
                        }, $finCarbon);
                        $nbJourSemaine += $debutCarbon->diffInDaysFiltered(function (Carbon $date) {
                            return !$date->isWeekend();
                        }, $finCarbon);
                    }

                    // Ajout des jours complet de présence
                    if ($testWeekend && $testNuit) {
                        $dureeTarifStandard += $nbJourSemaine * (24 - $dureeNuit);
                        $dureeTarifWeekend += $nbJourWeekend * 24;
                        $dureeTarifNuit += $nbJourSemaine * $dureeNuit;
                    } elseif ($testWeekend) {
                        $dureeTarifWeekend += $nbJourWeekend * 24;
                        $dureeTarifNuit += $nbJourSemaine * 24;
                    } elseif ($testNuit) {
                        $dureeTarifStandard += ($nbJourSemaine + $nbJourWeekend) * (24 - $dureeNuit);
                        $dureeTarifNuit += ($nbJourSemaine + $nbJourWeekend) * $dureeNuit;
                    } else {
                        $dureeTarifStandard += ($nbJourSemaine + $nbJourWeekend) * 24;
                    }

                    // Définition des deux périodes de nuit qui peuvent potentiellement overlap sur la présence
                    $diff = $debutNuit->diffInDays($debutCarbon, false);
                    $nightPeriodOneStart = $debutNuit->copy()->addDays($diff);
                    $nightPeriodOneEnd = $finNuit->copy()->addDays($diff);
                    $nightPeriodTwoStart = $nightPeriodOneStart->copy()->subDay();
                    $nightPeriodTwoEnd = $nightPeriodOneEnd->copy()->subDay();

                    if ($debutCarbon->copy()->subDay() >= $finCarbon) {
                        // Debut et fin la même journée
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
                        // Période portant sur deux jours

                        // Modification de la durée
                        $finJour = $debut->copy()->ceilDay();
                        $duree = $debut->floatDiffInHours($finJour);

                        // Premier jour de la présence -> début
                        if ($debutCarbon->isWeekend() && $testWeekend) {
                            $dureeTarifWeekend += $duree;
                        } elseif ($testNuit) {
                            $overlapping = 0;

                            // Create period 1 start and end date
                            $overlapping += max($debut->max($nightPeriodOneStart)->floatDiffInHours($finJour->min($nightPeriodOneEnd), false), 0);
                            $overlapping += max($debut->max($nightPeriodTwoStart)->floatDiffInHours($finJour->min($nightPeriodTwoEnd), false), 0);

                            $dureeTarifNuit += $overlapping;
                            $dureeTarifStandard += $duree - $overlapping;
                        } else {
                            $dureeTarifStandard += $duree;
                        }

                        // Deuxième jour de la présence -> fin

                        // Modification de la durée
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
            }

            // Calcul des tarifs
            $soldeStandard = $soldeTarif * $dureeTarifStandard;
            $soldeNuit = $soldeTarif * $dureeTarifNuit;
            $soldeWeekend = $soldeTarif * $dureeTarifWeekend;

            // Application des taux
            $soldeWeekend *= $tauxWeekend;
            $soldeNuit *= $tauxNuit;

            // Génération des écritures
            if ($soldeStandard > 0) {
                $ecritures[] = array(
                    'designation' => $designation,
                    'total' => $soldeStandard,
                    'tarif' => $soldeTarif,
                    'date' => $intervention->date_debut,
                    'heure' => $intervention->heure_debut,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'quantite' => $dureeTarifStandard,

                    'taux' => null,
                    'taux_description' => null,
                    'solde' => $soldeStandard,
                    'indemnite' => 0,
                    'frais' => 0,

                    'sapeur_id' => $sapeur_id,
                    'compte_id' => $indemniteType->compte_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $interventionId,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                );
            }

            if ($soldeNuit > 0) {
                $ecritures[] = [
                    'designation' => $designation . " - Nuit",
                    'total' => $soldeNuit,
                    'tarif' => $soldeTarif,
                    'date' => $intervention->date_debut,
                    'heure' => $intervention->heure_debut,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'quantite' => $dureeTarifNuit,

                    'taux' => $tauxNuit,
                    'taux_description' => 'Nuit',
                    'solde' => $soldeNuit,
                    'indemnite' => 0,
                    'frais' => 0,

                    'sapeur_id' => $sapeur_id,
                    'compte_id' => $indemniteType->compte_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $interventionId,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                ];
            }

            if ($soldeWeekend > 0) {
                $ecritures[] = [
                    'designation' => $designation . " - Weekend",
                    'total' => $soldeWeekend,
                    'tarif' => $soldeTarif,
                    'date' => $intervention->date_debut,
                    'heure' => $intervention->heure_debut,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'quantite' => $dureeTarifWeekend,

                    'taux' => $tauxWeekend,
                    'taux_description' => 'Weekend',
                    'solde' => $soldeWeekend,
                    'indemnite' => 0,
                    'frais' => 0,

                    'sapeur_id' => $sapeur_id,
                    'compte_id' => $indemniteType->compte_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $interventionId,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                ];
            }
        }

        Ecriture::insert($ecritures);
    }

    public function imputerExercice($exerciceId, $data)
    {
        $exercice = $this->exerciceRepo->getExerciceByIdWith($exerciceId, ['sapeurs', 'localite']);

        if ($exercice->statut !== ExerciceBusiness::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException(array("message" => "Impossible d'imputer cet exercice"));
        }

        $indemniteType = $this->indemniteRepo->findIndemniteExerciceTypeById($data['indemnite_exercice_type_id']);

        $unite = $indemniteType->type_unite_id;
        $designation = "{$exercice->localite->designation} ({$exercice->lieu}) $exercice->designation";
        $sapeurs = array_filter($exercice->sapeurs, function ($sap) {
            return $sap->present;
        });

        if ($unite === self::UNITE_CHF_PAR_PIECE) {
            $this->imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation);
        } elseif ($unite === self::UNITE_CHF_PAR_HEURE && $indemniteType->par_fonction) {
            $this->imputerExerciceParHeureEtFonction($exercice, $sapeurs, $indemniteType, $designation);
        } elseif ($unite === self::UNITE_CHF_PAR_HEURE) {
            $this->imputerExerciceParHeureEtSoldeMin($exercice, $sapeurs, $indemniteType, $designation);
        } else {
            dd("ERROR");
            return false;
            //TODO: WARNING IN LOGS -> Should never arrive here
        }

        // TODO: Ajout imputation heure supp !
        $heures = HeureExercice::where('exercice_id', $exerciceId)->get();
        $this->imputerExerciceHeureSup($exercice, $heures, $designation);

        // Changer le statut de l'exercice
        return $this->exerciceRepo->updateExerciceById($exerciceId, ["statut" => ExerciceBusiness::EXERCICE_STATUT_IMPUTE])->statut;
    }

    private function imputerExerciceHeureSup($exercice, $heures, $designation)
    {
        //TODO: solde_min should be null
        //En minutes
        $duree = $exercice->duree / 60;

        // Générer écritures
        foreach ($heures as $heure) {
            $designationSapeur = $designation . " - " . $heure->designation;

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
                'quantite' => $duree,
                'solde_min' => $indemniteType->solde_min,
                'solde_min_pour' => $indemniteType->solde_min_pour,
                'sapeur_id' => $sapeur->sapeur_id,
                'compte_id' => $indemniteType->compte_id,
                'exercice_comptable_id' => $exercice->exercice_comptable_id,
                'exercice_id' => $exercice->id,
                'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                'date' => $exercice->date,
                'heure' => $exercice->heure,
            );

            $this->ecritureRepo->persisteNewEcriture($ecriture);
        }
    }

    private function imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation)
    {
        // TODO: : solde_min should be null

        // Générer écritures
        $ecritures = [];
        foreach ($sapeurs as $sapeur) {
            $id = $this->sapeurRepo->getSapeurDetailsById($sapeur->sapeur_id)->fonction_id;

            $fonction_tarif = array_filter($indemniteType->fonctions, function ($f) use ($id) {
                return $f->fonction_id === $id;
            });

            $solde = 0;
            $indemnite = 0;
            if (count($fonction_tarif) > 0) {
                $tarif = array_pop($fonction_tarif);
                $solde += $tarif[0]->solde;
                $indemnite += $tarif[0]->indemnite;
            } else {
                $solde += $indemniteType->solde;
                $indemnite += $indemniteType->indemnite;
            }

            // Par pièce et pas par fonction -> pas de calcul
            $ecritures[] = [
                'solde' => $solde,
                'indemnite' => $indemnite,
                'frais' => 0,
                'type_unite_id' => $indemniteType->type_unite_id,
                'designation' => $designation,
                'total' => $solde + $indemnite,
                'tarif' => $solde + $indemnite,
                'quantite' => 1,
                'sapeur_id' => $sapeur->sapeur_id,
                'compte_id' => $indemniteType->compte_id,
                'exercice_comptable_id' => $exercice->exercice_comptable_id,
                'exercice_id' => $exercice->id,
                'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                'date' => $exercice->date,
                'heure' => $exercice->heure,
            ];
        }
        Ecriture::insert($ecritures);
    }

    private function imputerExerciceParHeureEtFonction($exercice, $sapeurs, $indemniteType, $designation)
    {
        //TODO: solde_min should be null
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
                $tarif = array_pop($fonction_tarif);
                $soldeTarif = $tarif->solde;
                $indemniteTarif = $tarif->indemnite;
            } else {
                $soldeTarif = $indemniteType->solde;
                $indemniteTarif = $indemniteType->indemnite;
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
                'quantite' => $duree,
                'solde_min' => $indemniteType->solde_min,
                'solde_min_pour' => $indemniteType->solde_min_pour,
                'sapeur_id' => $sapeur->sapeur_id,
                'compte_id' => $indemniteType->compte_id,
                'exercice_comptable_id' => $exercice->exercice_comptable_id,
                'exercice_id' => $exercice->id,
                'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                'date' => $exercice->date,
                'heure' => $exercice->heure,
            );

            $this->ecritureRepo->persisteNewEcriture($ecriture);
        }
    }

    private function imputerExerciceParHeureEtSoldeMin($exercice, $sapeurs, $indemniteType, $designation)
    {
        //En minutes
        $duree = $exercice->duree / 60;

        $solde = 0;
        if ($indemniteType->solde_min == null) {
            $indemniteType->solde_min = 0;
            $indemniteType->solde_min_pour = 0;
        }

        if ($duree > $indemniteType->solde_min_pour) {
            $solde += $indemniteType->solde_min;
            $duree -= $indemniteType->solde_min_pour;
        }

        $solde += $indemniteType->solde * $duree;

        // Générer écritures
        foreach ($sapeurs as $sapeur) {
            //Par heure -> calcul de la durée
            $ecriture = array(
                'solde' => $solde,
                'indemnite' => 0,
                'frais' => 0,
                'type_unite_id' => $indemniteType->type_unite_id,
                'designation' => $designation,
                'total' => $solde,
                'tarif' => $indemniteType->solde,
                'quantite' => $duree,
                'solde_min' => $indemniteType->solde_min,
                'solde_min_pour' => $indemniteType->solde_min_pour,
                'sapeur_id' => $sapeur->sapeur_id,
                'compte_id' => $indemniteType->compte_id,
                'exercice_comptable_id' => $exercice->exercice_comptable_id,
                'exercice_id' => $exercice->id,
                'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                'date' => $exercice->date,
                'heure' => $exercice->heure,
            );

            $this->ecritureRepo->persisteNewEcriture($ecriture);
        }
    }
}
