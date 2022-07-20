<?php

namespace App\Domaine\Business;

use App\Domaine\SPI\EcritureRepository;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\SPI\IndemniteTypeRepository;
use App\Domaine\SPI\InterventionRepository;
use App\Domaine\SPI\SapeurRepository;
use App\Domaine\Exceptions\ArrayException;
use Carbon\Carbon;
use App\Infrastructure\Models\Amende;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\ExcuseType;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceComptable;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\FonctionSapeur;
use App\Infrastructure\Models\HeureExercice;
use App\Infrastructure\Models\Intervention;

class ImputationBusiness
{
    protected $ecritureRepo;
    protected $indemniteRepo;
    protected $exerciceRepo;
    protected $interventionRepo;

    public function __construct(
        EcritureRepository $ecriture,
        SapeurRepository $sapeur,
        ExerciceRepository $exercice,
        InterventionRepository $intervention,
        IndemniteTypeRepository $indemnite
    ) {
        $this->ecritureRepo = $ecriture;
        $this->exerciceRepo = $exercice;
        $this->sapeurRepo = $sapeur;
        $this->interventionRepo = $intervention;
        $this->indemniteRepo = $indemnite;
    }

    // Unités de GestSIS
    public const UNITE_PIECE = 1;
    public const UNITE_HEURE = 2;
    public const UNITE_AN = 3;
    public const UNITE_KM = 4;
    public const UNITE_JOUR = 5;
    public const UNITE_FORFAIT = 6;
    public const UNITE_MOIS = 7;

    // Module ayant généré l'écriture
    public const ECRITURE_MODULE_DIVERS = 0;
    public const ECRITURE_MODULE_EXERCICE = 1;
    public const ECRITURE_MODULE_INTERVENTION = 2;
    public const ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL = 3;
    public const ECRITURE_MODULE_AVS = 4;
    public const ECRITURE_MODULE_AMENDE = 5;
    public const ECRITURE_MODULE_DECOMPTE_HEURE = 6;
    public const ECRITURE_MODULE_COURS = 7;
    public const ECRITURE_MODULE_REMBOURSEMENT = 8;

    // Type de catégorie d'imposition
    public const ECRITURE_CATEGORIE_IMPOSITION_AUTRE = 0; // Non pris en compte (amendes, ...)
    public const ECRITURE_CATEGORIE_IMPOSITION_SOLDE = 1; // Franchise configurable non imposable
    public const ECRITURE_CATEGORIE_IMPOSITION_INDEMNITE = 2; // Imposable dès le premier franc
    public const ECRITURE_CATEGORIE_IMPOSITION_FRAIS_FORFAITAIRE = 3; // Frais forfaitaire
    public const ECRITURE_CATEGORIE_IMPOSITION_FRAIS_EFFECTIF = 4; // Frais effectif
    public const ECRITURE_CATEGORIE_IMPOSITION_CHARGE_AVS_AC = 5; // Charges sociales

    private function arrondi_5_centimes($number)
    {
        $precision = 0.05;
        return round(round($number / $precision) * $precision, 2);
    }

    public function creerExerciceComptable($data)
    {
        $exerciceComptable = new ExerciceComptable();
        $exerciceComptable->fill($data);
        $exerciceComptable->boucle = 0;
        $exerciceComptable->save();
        return $exerciceComptable;
    }

    public function ajouterEcriture($data)
    {
        //TODO: Controller exercice comptable non clôturé

        // Switch between type
        switch ($data['module']) {
            case self::ECRITURE_MODULE_DIVERS:

                $ecriture = new Ecriture([
                    'tarif' => $data['tarif'],
                    'quantite' => $data['quantite'],
                    'total' => $this->arrondi_5_centimes($data['total']),

                    'designation' => $data['designation'],

                    'sapeur_id' => $data['sapeur_id'],
                    'compte_id' => $data['compte_id'],
                    'type_unite_id' => $data['type_unite_id'],
                    'exercice_comptable_id' => $data['exercice_comptable_id'],
                    'ecriture_categorie_id' => $data['ecriture_categorie_id'],

                    'decompte_id' => null,
                    'date' => $data['date'], // FIXME: check if null

                    'module' => self::ECRITURE_MODULE_DIVERS,
                    'type' => $data['type'],
                ]);

                $ecriture->save();
                return $ecriture;
                // break;

            default:
                throw new ArrayException([], 'Type d\'écriture non supporté pour le moment');
        }
    }

    public function modifierEcriture($ecritureId, $data)
    {
        $ecriture = Ecriture::find($ecritureId);
        // Contrôle que l'écriture n'est pas liée à un décompte
        if ($ecriture->decompte_id) {
            throw new ArrayException([], 'Ecriture déjà payée dans un décompte !');
        }

        // Switch between type
        switch ($data['module']) {
            case self::ECRITURE_MODULE_DIVERS:

                $ecriture->update([
                    'tarif' => $data['tarif'],
                    'quantite' => $data['quantite'],
                    'total' => $this->arrondi_5_centimes($data['total']),

                    'designation' => $data['designation'],

                    'sapeur_id' => $data['sapeur_id'],
                    'compte_id' => $data['compte_id'],
                    'type_unite_id' => $data['type_unite_id'],
                    'exercice_comptable_id' => $data['exercice_comptable_id'],
                    'ecriture_categorie_id' => $data['ecriture_categorie_id'],

                    'decompte_id' => null,
                    'date' => $data['date'], // FIXME: check if null
                    'type' => $data['type'],
                ]);

                $ecriture->save();
                return $ecriture;

            default:
                throw new ArrayException([], 'Type d\'écriture non supporté pour le moment');
        }
    }

    public function supprimerEcriture($ecritureId)
    {
        $ecriture = Ecriture::find($ecritureId);
        // Contrôle que l'écriture n'est pas liée à un décompte
        if ($ecriture->decompte_id) {
            throw new ArrayException([], 'Ecriture déjà payée dans un décompte !');
        }

        $ecriture->delete();
        return 'ok';
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

        $excusesTypes = ExcuseType::all();
        $indexedExcuses = [];
        foreach ($excusesTypes as $excuse) {
            $indexedExcuses[$excuse->id] = $excuse;
        }

        // Chargement des exercices amendés du sapeur
        $exercices = ExerciceSapeur::where([
            ['sapeur_id', '=', $sapeurId],
            ['amende', '=', 1]
        ])->join('exercices', 'exercices.id', '=', 'exercice_sapeur.exercice_id')
            ->where('exercices.exercice_comptable_id', $exerciceComptableId)
            ->orderBy('exercices.date', 'ASC')
            ->orderBy('exercices.heure')
            ->get();

        // Suppression de amendes existantes
        Ecriture::where([
            ['exercice_comptable_id', '=', $exerciceComptableId],
            ['sapeur_id', '=', $sapeurId],
            ['module', '=', self::ECRITURE_MODULE_AMENDE]
        ])->delete();

        // Pour l'instant juste générer de nouvelles amendes
        $ecritures = [];
        $i = 0;
        foreach ($exercices as $exercice) {

            $amende = $amendes[$i];

            // Creation d'une écriture pour chaque exercice amendé
            $ecriture = array(
                'tarif' => $amende->montant,
                'quantite' => 1,
                'total' => $amende->montant,

                'designation' => $exercice->designation,
                'complement' => $exercice->excuse_type_id ? $indexedExcuses[$exercice->excuse_type_id]->designation : "",

                'sapeur_id' => $exercice->sapeur_id,
                'exercice_id' => $exercice->exercice_id,
                'compte_id' => $amende->compte_id,
                'exercice_comptable_id' => $exerciceComptableId,
                'ecriture_categorie_id' => $amende->ecriture_categorie_id,

                'decompte_id' => null,
                'date' => $exercice->date,
                'heure' => $exercice->heure,

                'module' => self::ECRITURE_MODULE_AMENDE,
                'type' => self::ECRITURE_CATEGORIE_IMPOSITION_AUTRE,
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
            ->orderBy('exercices.heure')
            ->get();

        $excusesTypes = ExcuseType::all();
        $indexedExcuses = [];
        foreach ($excusesTypes as $excuse) {
            $indexedExcuses[$excuse->id] = $excuse;
        }

        // Suppression de amendes existantes
        Ecriture::where([
            ['exercice_comptable_id', '=', $exerciceComptableId],
            ['module', '=', self::ECRITURE_MODULE_AMENDE]
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
                'tarif' => $amende->montant,
                'quantite' => 1,
                'total' => $amende->montant,
                'type_unite_id' => self::UNITE_PIECE,

                'designation' => $exercice->designation,
                'complement' => $exercice->excuse_type_id ? $indexedExcuses[$exercice->excuse_type_id]->designation : "",

                'sapeur_id' => $exercice->sapeur_id,
                'exercice_id' => $exercice->exercice_id,
                'intervention_id' => null,
                'compte_id' => $amende->compte_id,
                'exercice_comptable_id' => $exerciceComptableId,
                'ecriture_categorie_id' => $amende->ecriture_categorie_id,

                'decompte_id' => null,
                'date' => $exercice->date,
                'heure' => $exercice->heure,

                'module' => self::ECRITURE_MODULE_AMENDE,
                'type' => self::ECRITURE_CATEGORIE_IMPOSITION_AUTRE,
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
        // FIXME: Actuelle regénère les frais pour tous les sapeurs ! et ne fait pas ce qui est écrit ci-dessous
        // 1. ~~ Si déjà une imputation pour l'année alors ne rien faire~~
        // 2. OUI -> Ajouter des imputations uniquement pour les sapeurs qui n'ont pas de frais pour l'instant
        // 3. ~~ Tout supprimer pour l'année courante et tout regénérer~~
        //
        // Notes :
        // - Ne prend actuellement en compte que la fonction actuelle et non pas la date de l'entrée en vigeure de cette fonction
        // - Prend uniquement les sapeurs actifs

        $fraisIndemnitesTypes = $this->indemniteRepo->listeFraisIndemniteAnnuelType();

        // FIXME: regénérer que pour les sapeurs ne possédants pas d'indemnités ???
        $ecritures = $this->ecritureRepo->listeEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);
        Ecriture::where('module', self::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL)->whereNull('decompte_id')->delete();
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
        foreach ($fraisIndemnitesTypes as $type) {

            // Génère le mapping -> ["fonction_id" => 'indemnite'];
            $mapping = array_reduce(array_map(
                fn ($indemnite) => [$indemnite['fonction_id'] => $indemnite],
                $type['frais_indemnite_annuels']
            ), fn ($a, $b) => $a + $b, []);

            foreach ($sapeursGrouped as $sapeurId => $fonctions) {
                foreach ($fonctions as $fonctionId) {
                    if (array_key_exists($fonctionId, $mapping)) {
                        $indemnite = $mapping[$fonctionId];
                        $this->imputerFraisIndemniteSapeur($type, $indemnite, $sapeurId, $exerciceComptableId);

                        if (!$type['cumulable']) {
                            // Non-cumulable, on passe au sapeur suivant
                            break;
                        }
                    }
                }
            }
        }
    }

    private function imputerFraisIndemniteSapeur($fraisIndemniteType, $indemnite, $sapeurId, $exerciceComptableId)
    {
        $ecriture = array(
            'tarif' => $indemnite['montant'],
            'quantite' => $indemnite['quantite'],
            'total' => $this->arrondi_5_centimes($indemnite['montant'] * $indemnite['quantite']),

            'type_unite_id' => $indemnite['type_unite_id'],
            'designation' => $fraisIndemniteType['designation'],
            'sapeur_id' => $sapeurId,
            'compte_id' => $fraisIndemniteType['compte_id'],
            'exercice_comptable_id' => $exerciceComptableId,
            'ecriture_categorie_id' => $fraisIndemniteType['ecriture_categorie_id'],

            'module' => self::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL,
            'type' => $fraisIndemniteType['type'],
        );

        $this->ecritureRepo->persisteNewEcriture($ecriture);
    }

    public function annulerImputationExercice($exerciceId)
    {
        // Check si des ecritures sont déjà liées à un décompte
        if (Ecriture::where('exercice_id', $exerciceId)
            ->whereNotNull('decompte_id')
            ->exists()
        ) {
            throw new ArrayException([], 'Des écriture sont déjà facturées dans un décompte.');
        }

        // Suppression des écritures
        Ecriture::where('exercice_id', $exerciceId)
            ->delete();

        // Modification du statut de l'exercice
        Exercice::where('id', $exerciceId)->update(['statut' => ExerciceBusiness::EXERCICE_STATUT_VALIDE]);
        return ExerciceBusiness::EXERCICE_STATUT_VALIDE;
    }

    public function annulerImputationIntervention($interventionId)
    {
        // Check si des ecritures sont déjà liées à un décompte
        if (Ecriture::where('intervention_id', $interventionId)
            ->whereNotNull('decompte_id')
            ->exists()
        ) {
            throw new ArrayException([], 'Des écriture sont déjà facturées dans un décompte.');
        }

        // Suppression des écritures
        Ecriture::where('intervention_id', $interventionId)
            ->delete();

        // Modification du statut de l'intervention
        Intervention::where('id', $interventionId)->update(['statut' => InterventionBusiness::INTERVENTION_STATUT_VALIDE]);
        return InterventionBusiness::INTERVENTION_STATUT_VALIDE;
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
            $this->imputerInterventionTarifMin($interventionId, $intervention, $indemniteType, $data);
        }

        // Update statut
        return $this->interventionRepo->editInterventionInformationsById($interventionId, [
            "statut" => InterventionBusiness::INTERVENTION_STATUT_IMPUTE
        ])->statut;
    }

    private function imputerInterventionTarifMin($interventionId, $intervention, $indemniteType, $data)
    {
        // Grouper les présences par sapeurs
        $sapeurs = [];
        foreach ($intervention->presences as $presence) {
            if (!array_key_exists($presence->sapeur_id, $sapeurs)) {
                $sapeurs[$presence->sapeur_id] = [];
            }
            array_push($sapeurs[$presence->sapeur_id], $presence);
        }

        $phases = collect($intervention->phases)->sortByDesc('debut');

        $dureeTarifMin = array();
        $dureeNonTarifMin = array();

        $indemnite_phase_id = $indemniteType->phase_id;

        // Sépare les période de chaque sapeur entre les différentes phases
        foreach ($sapeurs as $sapeurId => $presences) {
            $dureeTarifMinSapeur = 0;
            $dureeNonTarifMinSapeur = 0;
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
                        $dureeTarifMinSapeur += $duree;
                    } else {
                        $dureeNonTarifMinSapeur += $duree;
                    }
                    break;
                }
            }
            $dureeTarifMin[$sapeurId] = ($dureeTarifMin[$sapeurId] ?? 0) + $dureeTarifMinSapeur;
            $dureeNonTarifMin[$sapeurId] = ($dureeNonTarifMin[$sapeurId] ?? 0) + $dureeNonTarifMinSapeur;
        }

        // Récupération du type de frais
        $tarif = floatval($indemniteType->tarif);
        $tarifMin = floatval($indemniteType->tarif_min ?? $indemniteType->tarif);
        $tarifMinPour = floatval($indemniteType->tarif_min_pour) ?? 1.0;
        $designation = "{$intervention->localite->designation} ({$intervention->type->designation}) $intervention->lieu";

        $ecritures = array();
        foreach ($dureeTarifMin as $sapeurId => $dureeTarifMinSapeur) {
            // Duree sans tarif min
            $dureeNonTarifMinSapeur = $dureeNonTarifMin[$sapeurId];

            $total = 0;

            // Application du tarif min
            if ($dureeTarifMinSapeur > $tarifMinPour) {
                $dureeNonTarifMinSapeur += $dureeTarifMinSapeur - $tarifMinPour;
                $dureeTarifMinSapeur = $tarifMinPour;
            }

            // Calcul du tarif min au pro-rata ou pas dans le cas ou la duree effective est plus petite que la duree min
            if ($indemniteType->tarif_min_pro_rata && $tarifMinPour > 0) {
                $total += $tarifMin / $tarifMinPour * $dureeTarifMinSapeur;
            } else {
                $total += $tarifMin;
            }
            $total += $tarif * $dureeNonTarifMinSapeur;

            $total = $this->arrondi_5_centimes($total);

            $ecritures[] = array(
                'tarif' => $tarif,
                'quantite' => $dureeTarifMinSapeur + $dureeNonTarifMinSapeur,
                'total' => $total,
                'tarif_min' => $tarifMin,
                'tarif_min_pour' => $tarifMinPour,
                'tarif_min_pro_rata' => $indemniteType->tarif_min_pro_rata,

                'type_unite_id' => $indemniteType->type_unite_id,
                'designation' => $designation,
                'sapeur_id' => $sapeurId,
                'compte_id' => $indemniteType->compte_id,
                'exercice_comptable_id' => $intervention->exercice_comptable_id,
                'intervention_id' => $intervention->id,
                'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                'date' => $intervention->date_debut,
                'heure' => $intervention->heure_debut,

                'module' => self::ECRITURE_MODULE_INTERVENTION,
                'type' => $indemniteType->type,
            );
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
            $tarif = floatVal($indemniteType->tarif);
            $tauxWeekend = floatVal($indemniteType->taux_weekend);
            $tauxNuit = floatVal($indemniteType->taux_nuit);

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

            // Calcul des totaux
            $totalTarifStandard = $this->arrondi_5_centimes($tarif * $dureeTarifStandard);
            $totalTarifNuit = $this->arrondi_5_centimes($tarif * $dureeTarifNuit * $tauxNuit);
            $totalTarifWeekend = $this->arrondi_5_centimes($tarif * $dureeTarifWeekend * $tauxWeekend);

            // Génération des écritures
            if ($totalTarifStandard > 0) {
                $ecritures[] = array(
                    'tarif' => $tarif,
                    'quantite' => $dureeTarifStandard,
                    'taux' => null,
                    'taux_description' => null,
                    'total' => $totalTarifStandard,

                    'designation' => $designation,
                    'date' => $intervention->date_debut,
                    'heure' => $intervention->heure_debut,
                    'type_unite_id' => $indemniteType->type_unite_id,

                    'sapeur_id' => $sapeur_id,
                    'compte_id' => $indemniteType->compte_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $interventionId,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,

                    'module' => self::ECRITURE_MODULE_INTERVENTION,
                    'type' => $indemniteType->type,
                );
            }

            if ($totalTarifNuit > 0) {
                $ecritures[] = [
                    'tarif' => $tarif,
                    'quantite' => $dureeTarifNuit,
                    'taux' => $tauxNuit,
                    'taux_description' => 'Nuit',
                    'total' => $totalTarifNuit,

                    'designation' => $designation . " - Nuit",
                    'date' => $intervention->date_debut,
                    'heure' => $intervention->heure_debut,
                    'type_unite_id' => $indemniteType->type_unite_id,

                    'sapeur_id' => $sapeur_id,
                    'compte_id' => $indemniteType->compte_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $interventionId,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,

                    'module' => self::ECRITURE_MODULE_INTERVENTION,
                    'type' => $indemniteType->type,
                ];
            }

            if ($totalTarifWeekend > 0) {
                $ecritures[] = [
                    'tarif' => $tarif,
                    'quantite' => $dureeTarifWeekend,
                    'taux' => $tauxWeekend,
                    'taux_description' => 'Weekend',
                    'total' => $totalTarifWeekend,

                    'designation' => $designation . " - Weekend",
                    'date' => $intervention->date_debut,
                    'heure' => $intervention->heure_debut,
                    'type_unite_id' => $indemniteType->type_unite_id,

                    'sapeur_id' => $sapeur_id,
                    'compte_id' => $indemniteType->compte_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $interventionId,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,

                    'module' => self::ECRITURE_MODULE_INTERVENTION,
                    'type' => $indemniteType->type,
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

        if ($unite === self::UNITE_PIECE || $unite === self::UNITE_FORFAIT) {
            $this->imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation);
        } else if ($unite === self::UNITE_HEURE) {
            $this->imputerExerciceParHeure($exercice, $sapeurs, $indemniteType, $designation);
        } else {
            throw new ArrayException(["message" => "Unité non supportée"]);
        }

        // Imputation heure supp !
        $heures = HeureExercice::where('exercice_id', $exerciceId)->get();
        $this->imputerExerciceHeureSup($exercice, $heures, $designation);

        // Changer le statut de l'exercice
        return $this->exerciceRepo->updateExerciceById($exerciceId, ["statut" => ExerciceBusiness::EXERCICE_STATUT_IMPUTE])->statut;
    }

    private function imputerExerciceHeureSup($exercice, $heures, $designation)
    {
        // Générer écritures
        foreach ($heures as $heure) {
            $designationSapeur = $designation . " - " . $heure->designation;
            $total = $heure->quantite * $heure->montant;

            // Par heure -> calcul de la durée
            $ecriture = array(
                'tarif' => $heure->montant,
                'quantite' => $heure->quantite,
                'tarif_min' => null,
                'tarif_min_pour' => null,
                'total' => $total,

                'designation' => $designationSapeur,
                'type_unite_id' => $heure->type_unite_id,
                'sapeur_id' => $heure->sapeur_id,
                'compte_id' => $heure->compte_id,
                'exercice_comptable_id' => $exercice->exercice_comptable_id,
                'exercice_id' => $exercice->id,
                'ecriture_categorie_id' => $heure->ecriture_categorie_id,
                'date' => $exercice->date,
                'heure' => $exercice->heure,

                'module' => self::ECRITURE_MODULE_EXERCICE,
                'type' => $heure->type,
            );

            $this->ecritureRepo->persisteNewEcriture($ecriture);
        }
    }

    private function imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation)
    {
        // TODO: : tarif_min should be null

        // Générer écritures
        $ecritures = [];
        foreach ($sapeurs as $sapeur) {
            // Utilise uniquement la fonction principale
            $id = $this->sapeurRepo->getSapeurDetailsById($sapeur->sapeur_id)->fonction_id;

            $fonction_tarifs = array_filter($indemniteType->fonctions, function ($f) use ($id) {
                return $f->fonction_id === $id;
            });

            if (count($fonction_tarifs) == 0) {
                $fonction_tarifs = array_filter($indemniteType->fonctions, function ($f) {
                    return $f->fonction_id === null;
                });
            }

            foreach ($fonction_tarifs as $indemnite) {
                // Par pièce et pas par fonction -> pas de calcul
                $ecritures[] = [
                    'tarif' => $indemnite->tarif,
                    'quantite' => 1,
                    'tarif_min' => null,
                    'tarif_min_pour' => null,
                    'total' => $indemnite->tarif,

                    'compte_id' => $indemnite->compte_id,

                    'designation' => $designation,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'sapeur_id' => $sapeur->sapeur_id,
                    'exercice_comptable_id' => $exercice->exercice_comptable_id,
                    'exercice_id' => $exercice->id,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                    'date' => $exercice->date,
                    'heure' => $exercice->heure,

                    'module' => self::ECRITURE_MODULE_EXERCICE,
                    'type' => $indemnite->type,
                ];
            }
        }
        Ecriture::insert($ecritures);
    }

    private function imputerExerciceParHeure($exercice, $sapeurs, $indemniteType, $designation)
    {
        $duree = $exercice->duree / 60;

        // Générer écritures
        foreach ($sapeurs as $sapeur) {
            $id = $this->sapeurRepo->getSapeurDetailsById($sapeur->sapeur_id)->fonction_id;

            $fonction_tarifs = array_filter($indemniteType->fonctions, function ($f) use ($id) {
                return $f->fonction_id === $id;
            });

            if (count($fonction_tarifs) == 0) {
                $fonction_tarifs = array_filter($indemniteType->fonctions, function ($f) {
                    return $f->fonction_id === null;
                });
            }

            foreach ($fonction_tarifs as $indemnite) {

                $total = 0;
                if ($indemnite->tarif_min == null) {
                    $total = $indemnite->tarif * $duree;
                } else if ($duree < $indemnite->tarif_min_pour) {
                    $total = $indemnite->tarif_min * ($duree / $indemnite->tarif_min_pour);
                } else {
                    $total = $indemnite->tarif_min + $indemnite->tarif * ($duree - $indemnite->tarif_min_pour);
                }

                //Par heure -> calcul de la durée
                $ecriture = array(
                    'tarif' => $indemnite->tarif,
                    'quantite' => $duree,
                    'tarif_min' => $indemniteType->tarif_min,
                    'tarif_min_pour' => $indemniteType->tarif_min_pour,
                    'total' => $total,

                    'compte_id' => $indemnite->compte_id,

                    'designation' => $designation,
                    'type_unite_id' => $indemniteType->type_unite_id,
                    'sapeur_id' => $sapeur->sapeur_id,
                    'exercice_comptable_id' => $exercice->exercice_comptable_id,
                    'exercice_id' => $exercice->id,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                    'date' => $exercice->date,
                    'heure' => $exercice->heure,

                    'module' => self::ECRITURE_MODULE_EXERCICE,
                    'type' => $indemnite->type,
                );

                $this->ecritureRepo->persisteNewEcriture($ecriture);
            }
        }
    }
}
