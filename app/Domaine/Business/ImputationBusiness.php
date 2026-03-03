<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\InvalidActionException;
use App\Domaine\SPI\ExerciceRepository;
use App\Domaine\SPI\InterventionRepository;
use App\Domaine\SPI\SapeurRepository;
use App\Domaine\Exceptions\ArrayException;
use Carbon\Carbon;
use App\Infrastructure\Models\Amende;
use App\Infrastructure\Models\CoursSapeur;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\ExcuseType;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceComptable;
use App\Infrastructure\Models\ExerciceSapeur;
use App\Infrastructure\Models\Fonction;
use App\Infrastructure\Models\FonctionSapeur;
use App\Infrastructure\Models\FraisIndemniteAnnuelType;
use App\Infrastructure\Models\HeureExercice;
use App\Infrastructure\Models\HeureExerciceType;
use App\Infrastructure\Models\IndemniteCoursType;
use App\Infrastructure\Models\IndemniteExerciceType;
use App\Infrastructure\Models\IndemniteInterventionType;
use App\Infrastructure\Models\Intervention;
use App\Infrastructure\Models\Travail;
use App\Infrastructure\Models\TravailType;

class ImputationBusiness
{
    protected $exerciceRepo;
    protected $sapeurRepo;
    protected $interventionRepo;

    public function __construct(
        SapeurRepository $sapeur,
        ExerciceRepository $exercice,
        InterventionRepository $intervention
    ) {
        $this->exerciceRepo = $exercice;
        $this->sapeurRepo = $sapeur;
        $this->interventionRepo = $intervention;
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
    public const ECRITURE_MODULE_FICHE_TRAVAIL = 6;
    public const ECRITURE_MODULE_COURS = 7;
    public const ECRITURE_MODULE_REMBOURSEMENT = 8;

    // Type de catégorie d'imposition
    public const ECRITURE_CATEGORIE_IMPOSITION_AUTRE = 0; // Non pris en compte (amendes, ...)
    public const ECRITURE_CATEGORIE_IMPOSITION_SOLDE = 1; // Franchise configurable non imposable
    public const ECRITURE_CATEGORIE_IMPOSITION_INDEMNITE = 2; // Imposable dès le premier franc
    public const ECRITURE_CATEGORIE_IMPOSITION_FRAIS_FORFAITAIRE = 3; // Frais forfaitaire
    public const ECRITURE_CATEGORIE_IMPOSITION_FRAIS_EFFECTIF = 4; // Frais effectif
    public const ECRITURE_CATEGORIE_IMPOSITION_CHARGE_AVS_AC = 5; // Charges sociales

    public static function arrondi_5_centimes($number)
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

    public function controlerStatusExerciceComptable(int $exerciceComptableId): void
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        if ($exerciceComptable === null) {
            throw new ArrayException([], 'Exercice comptable introuvable');
        }
        if ($exerciceComptable->boucle === 1) {
            throw new InvalidActionException(message: "Exercice comptable clôturé, impossible d'effectuer cette action");
        }
    }

    public function ajouterEcriture($data)
    {
        // Validation et recalcul du total
        $totalCalcule = $data['tarif'] * $data['quantite'];
        $totalAttendu = self::arrondi_5_centimes($totalCalcule);
        if (abs($totalAttendu - $data['total']) > 0.01) {
            throw new ArrayException([], "Le total fourni ({$data['total']}) ne correspond pas au calcul (tarif * quantité = {$totalAttendu})");
        }

        $this->controlerStatusExerciceComptable($data['exercice_comptable_id']);

        // Seul le module DIVERS est supporté actuellement
        if ($data['module'] !== self::ECRITURE_MODULE_DIVERS) {
            throw new ArrayException([], 'Type d\'écriture non supporté pour le moment');
        }

        $ecriture = new Ecriture([
            'tarif' => $data['tarif'],
            'quantite' => $data['quantite'],
            'total' => $totalAttendu,
            'designation' => $data['designation'],
            'sapeur_id' => $data['sapeur_id'],
            'compte_id' => $data['compte_id'],
            'type_unite_id' => $data['type_unite_id'],
            'exercice_comptable_id' => $data['exercice_comptable_id'],
            'ecriture_categorie_id' => $data['ecriture_categorie_id'],
            'decompte_id' => null,
            'date' => $data['date'],
            'module' => self::ECRITURE_MODULE_DIVERS,
            'type' => $data['type'],
        ]);

        $ecriture->save();
        return $ecriture;
    }

    public function modifierEcriture($ecritureId, $data)
    {
        // Validation et recalcul du total
        $totalCalcule = $data['tarif'] * $data['quantite'];
        $totalAttendu = self::arrondi_5_centimes($totalCalcule);
        if (abs($totalAttendu - $data['total']) > 0.01) {
            throw new ArrayException([], "Le total fourni ({$data['total']}) ne correspond pas au calcul (tarif * quantité = {$totalAttendu})");
        }

        // Validation des montants positifs
        if ($data['tarif'] < 0 || $data['quantite'] < 0) {
            throw new ArrayException([], 'Le tarif et la quantité doivent être positifs');
        }

        // Charger l'écriture d'abord
        $ecriture = Ecriture::find($ecritureId);
        if ($ecriture === null) {
            throw new ArrayException([], 'Écriture introuvable');
        }

        // Vérifier le statut de l'exercice comptable avec l'ID de l'écriture existante
        $this->controlerStatusExerciceComptable($ecriture->exercice_comptable_id);

        // Contrôle que l'écriture n'est pas liée à un décompte
        if ($ecriture->decompte_id !== null) {
            throw new ArrayException([], 'Écriture déjà payée dans un décompte !');
        }

        // Seul le module DIVERS est supporté actuellement
        if ($data['module'] !== self::ECRITURE_MODULE_DIVERS) {
            throw new ArrayException([], 'Type d\'écriture non supporté pour le moment');
        }

        $ecriture->update([
            'tarif' => $data['tarif'],
            'quantite' => $data['quantite'],
            'total' => $totalAttendu,
            'designation' => $data['designation'],
            'sapeur_id' => $data['sapeur_id'],
            'compte_id' => $data['compte_id'],
            'type_unite_id' => $data['type_unite_id'],
            'exercice_comptable_id' => $data['exercice_comptable_id'],
            'ecriture_categorie_id' => $data['ecriture_categorie_id'],
            'decompte_id' => null,
            'date' => $data['date'],
            'type' => $data['type'],
        ]);

        return $ecriture;
    }

    public function supprimerEcriture($ecritureId): string
    {
        $ecriture = Ecriture::find($ecritureId);
        if ($ecriture === null) {
            throw new ArrayException([], 'Écriture introuvable');
        }

        $this->controlerStatusExerciceComptable($ecriture->exercice_comptable_id);

        // Contrôle que l'écriture n'est pas liée à un décompte
        if ($ecriture->decompte_id !== null) {
            throw new ArrayException([], 'Écriture déjà payée dans un décompte !');
        }

        $ecriture->delete();
        return 'ok';
    }

    /**
     * Générer les amendes pour un sapeur
     */
    public function genererAmendesSapeur($exerciceComptableId, $sapeurId)
    {
        $this->controlerStatusExerciceComptable($exerciceComptableId);

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
            ['excuse_statut', '=', ExerciceBusiness::EXCUSE_STATUT_AMENDEE]
        ])->join('exercices', 'exercices.id', '=', 'exercice_sapeur.exercice_id')
            ->where('exercices.exercice_comptable_id', $exerciceComptableId)
            ->orderBy('exercices.date', 'ASC')
            ->orderBy('exercices.heure')
            ->get();

        // Vérifier qu'aucune amende n'est déjà payée
        if (
            Ecriture::where([
                ['exercice_comptable_id', '=', $exerciceComptableId],
                ['sapeur_id', '=', $sapeurId],
                ['module', '=', self::ECRITURE_MODULE_AMENDE]
            ])->whereNotNull('decompte_id')->exists()
        ) {
            throw new ArrayException([], 'Des amendes sont déjà facturées dans un décompte.');
        }

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
            $ecriture = [
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
            ];

            $ecritures[] = $ecriture;

            if ($i + 1 < $nbAmende) {
                $i++;
            }
        }

        if (!empty($ecritures)) {
            Ecriture::insert($ecritures);
        }
        return $ecritures;
    }

    /**
     * Générer les amendes pour l'année comptable en cours
     */
    public function genererAmendesAnnuels($exerciceComptableId)
    {
        $this->controlerStatusExerciceComptable($exerciceComptableId);

        // Chargment de la config des amendes
        $amendes = Amende::orderBy('ordre', 'ASC')->get();
        $nbAmende = count($amendes);

        if ($nbAmende <= 0) {
            throw new ArrayException(['config' => 'Pas de configurations d\'amendes'], "Aucune amende configurée");
        }

        // Chargement des exercices amendés du sapeur
        $exercices = ExerciceSapeur::where([
            ['excuse_statut', '=', ExerciceBusiness::EXCUSE_STATUT_AMENDEE]
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

        // Vérifier qu'aucune amende n'est déjà payée
        if (
            Ecriture::where([
                ['exercice_comptable_id', '=', $exerciceComptableId],
                ['module', '=', self::ECRITURE_MODULE_AMENDE]
            ])->whereNotNull('decompte_id')->exists()
        ) {
            throw new ArrayException([], 'Des amendes sont déjà facturées dans un décompte.');
        }

        // Suppression de amendes existantes
        Ecriture::where([
            ['exercice_comptable_id', '=', $exerciceComptableId],
            ['module', '=', self::ECRITURE_MODULE_AMENDE]
        ])->delete();

        // Pour l'instant juste générer de nouvelles amendes
        $newEcritures = [];
        $i = 0;
        $sapeurId = -1;

        foreach ($exercices as $exercice) {
            if ($sapeurId !== $exercice->sapeur_id) {
                $i = 0;
                $sapeurId = $exercice->sapeur_id;
            }

            $amende = $amendes[$i];

            // Creation d'une écriture pour chaque exercice amendé
            $ecriture = [
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
            ];

            $newEcritures[] = $ecriture;

            if ($i + 1 < $nbAmende) {
                $i++;
            }
        }

        if (!empty($newEcritures)) {
            Ecriture::insert($newEcritures);
        }
        return $newEcritures;
    }

    /**
     * Calcule le nombre de mois d'activité entre deux périodes (arrondi au mois entamé)
     */
    private function calculerNombreMoisActifs($debutFonction, $finFonction, $debutExercice, $finExercice): int
    {
        $debutExerciceCarbon = Carbon::parse($debutExercice);
        $finExerciceCarbon = Carbon::parse($finExercice);

        // Intersection des périodes
        $debutFonctionCarbon = Carbon::parse($debutFonction);
        $finFonctionCarbon = $finFonction ? Carbon::parse($finFonction) : null;

        $debut = $debutFonctionCarbon->max($debutExerciceCarbon);
        $fin = $finFonctionCarbon ? $finFonctionCarbon->min($finExerciceCarbon) : $finExerciceCarbon;

        if ($debut->gt($fin)) {
            return 0;
        }

        // Calcul du nombre de mois distincts touchés (tout mois entamé compte pour 1)
        // Méthode : calculer la différence entre (année*12 + mois) du début et de la fin
        $moisDebut = $debut->year * 12 + $debut->month;
        $moisFin = $fin->year * 12 + $fin->month;

        return $moisFin - $moisDebut + 1;
    }

    /**
     * Génères des frais annuels pour les sapeurs n'ayant pas encore de frais annuels
     */
    public function imputerAnnuel(int $exerciceComptableId)
    {
        $this->controlerStatusExerciceComptable($exerciceComptableId);

        // Choix disponible pour une seule imputation annuelle :
        // FIXME: Actuellement regénère les frais pour tous les sapeurs ! et ne fait pas ce qui est écrit ci-dessous
        // 1. ~~ Si déjà une imputation pour l'année alors ne rien faire~~
        // 2. OUI -> Ajouter des imputations uniquement pour les sapeurs qui n'ont pas de frais pour l'instant
        // 3. ~~ Tout supprimer pour l'année courante et tout regénérer~~
        //
        // Notes :
        // - Prend en compte la période d'entrée en vigueur de chaque fonction
        // - Pour les indemnités mensuelles (UNITE_MOIS), calcule le nombre de mois réels d'activité
        // - Prend uniquement les sapeurs actifs

        $fraisIndemnitesTypes = FraisIndemniteAnnuelType::with('fraisIndemniteAnnuels')->get();

        $fonctions = Fonction::all();
        $indexedFonctions = [];
        foreach ($fonctions as $fonction) {
            $indexedFonctions[$fonction['id']] = $fonction['nom'];
        }

        // FIXME: regénérer que pour les sapeurs ne possédants pas d'indemnités ???

        // Vérifier qu'aucune indemnité n'est déjà payée
        if (
            Ecriture::where([
                ['exercice_comptable_id', '=', $exerciceComptableId],
                ['module', '=', self::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL]
            ])->whereNotNull('decompte_id')->exists()
        ) {
            throw new ArrayException([], 'Des indemnités annuelles sont déjà facturées dans un décompte.');
        }

        // Suppression des indemnités annuelles existantes pour cet exercice comptable uniquement
        Ecriture::where([
            ['exercice_comptable_id', '=', $exerciceComptableId],
            ['module', '=', self::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL]
        ])->delete();

        // Exercice comptable
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        $debut = $exerciceComptable->debut;
        $fin = $exerciceComptable->fin;

        // Fonction gardée si intersect avec exercice comptable actuel
        $sapeurs = FonctionSapeur::where(function ($query) use ($debut, $fin) {
            $query
                ->where(function ($query) use ($debut) {
                    $query
                        ->where([
                            ['fonction_sapeur.debut', '<=', $debut],
                            ['fonction_sapeur.fin', '>=', $debut],
                        ])
                        ->whereNotNull('fonction_sapeur.fin');
                })
                ->orWhere(function ($query) use ($debut) {
                    $query
                        ->where('fonction_sapeur.debut', '<=', $debut)
                        ->whereNull('fonction_sapeur.fin');
                })
                ->orWhere(function ($query) use ($debut, $fin) {
                    $query
                        ->where([
                            ['fonction_sapeur.debut', '>=', $debut],
                            ['fonction_sapeur.debut', '<=', $fin],
                        ]);
                });
        })
            ->join('fonctions', 'fonctions.id', '=', 'fonction_sapeur.fonction_id')
            ->orderByDesc('fonctions.tri')
            ->distinct(['fonction_sapeur.sapeur_id', 'fonction_sapeur.fonction_id'])
            ->select(['fonction_sapeur.sapeur_id', 'fonction_sapeur.fonction_id', 'fonction_sapeur.debut', 'fonction_sapeur.fin', 'fonctions.tri'])->get();

        // Group by sapeur_id avec conservation des périodes de fonction
        $sapeursGrouped = [];
        foreach ($sapeurs as $sapeur) {
            $sapeursGrouped[$sapeur->sapeur_id][] = [
                'fonction_id' => $sapeur->fonction_id,
                'debut' => $sapeur->debut,
                'fin' => $sapeur->fin,
                'tri' => $sapeur->tri
            ];
        }

        // Foreach indemnité annuelle
        $ecritures = [];
        foreach ($fraisIndemnitesTypes as $type) {

            // Génère le mapping -> ["fonction_id" => 'indemnite'];
            $mapping = array_reduce(array_map(
                fn($indemnite) => [$indemnite['fonction_id'] => $indemnite],
                $type->fraisIndemniteAnnuels->toArray()
            ), fn($a, $b) => $a + $b, []);

            foreach ($sapeursGrouped as $sapeurId => $fonctions) {
                foreach ($fonctions as $fonction) {
                    $fonctionId = $fonction['fonction_id'];

                    if (array_key_exists($fonctionId, $mapping)) {
                        $indemnite = $mapping[$fonctionId];

                        // Calcul de la quantité et du total
                        $quantite = $indemnite['quantite'];
                        $tarif = $indemnite['montant'];

                        // Pour les unités mensuelles, calculer le nombre de mois réels d'activité
                        if ($indemnite['type_unite_id'] == self::UNITE_MOIS) {
                            $quantite = $this->calculerNombreMoisActifs(
                                $fonction['debut'],
                                $fonction['fin'],
                                $debut,
                                $fin
                            );

                            // Si aucun mois d'activité, ne pas générer d'écriture
                            if ($quantite <= 0) {
                                continue;
                            }
                        }

                        $total = self::arrondi_5_centimes($tarif * $quantite);

                        $ecritures[] = [
                            'tarif' => $tarif,
                            'quantite' => $quantite,
                            'total' => $total,
                            'type_unite_id' => $indemnite['type_unite_id'],
                            'designation' => $type->designation . ' (' . ($indexedFonctions[$indemnite['fonction_id']] ?? '') . ")",
                            'sapeur_id' => $sapeurId,
                            'compte_id' => $type->compte_id,
                            'exercice_comptable_id' => $exerciceComptableId,
                            'ecriture_categorie_id' => $type->ecriture_categorie_id,
                            'module' => self::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL,
                            'type' => $type->type,
                        ];

                        if (!$type->cumulable) {
                            // Non-cumulable, on passe au sapeur suivant
                            break;
                        }
                    }
                }
            }
        }

        if (!empty($ecritures)) {
            Ecriture::insert($ecritures);
        }
    }

    public function annulerImputationExercice($exerciceId)
    {
        $exercice = Exercice::find($exerciceId);
        if ($exercice === null) {
            throw new ArrayException([], "Exercice introuvable.");
        }
        $this->controlerStatusExerciceComptable($exercice->exercice_comptable_id);

        // Vérifier si des écritures sont déjà liées à un décompte
        if (
            Ecriture::where('exercice_id', $exerciceId)
                ->whereNotNull('decompte_id')
                ->exists()
        ) {
            throw new ArrayException([], 'Des écritures sont déjà facturées dans un décompte.');
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
        $intervention = Intervention::find($interventionId);
        if ($intervention === null) {
            throw new ArrayException([], "Intervention introuvable.");
        }
        $this->controlerStatusExerciceComptable($intervention->exercice_comptable_id);

        // Vérifier si des écritures sont déjà liées à un décompte
        if (
            Ecriture::where('intervention_id', $interventionId)
                ->whereNotNull('decompte_id')
                ->exists()
        ) {
            throw new ArrayException([], 'Des écritures sont déjà facturées dans un décompte.');
        }

        // Suppression des écritures
        Ecriture::where('intervention_id', $interventionId)
            ->delete();

        // Modification du statut de l'intervention
        Intervention::where('id', $interventionId)->update(['statut' => InterventionBusiness::INTERVENTION_STATUT_VALIDE]);
        return InterventionBusiness::INTERVENTION_STATUT_VALIDE;
    }

    public function annulerImputationAnnuel($exerciceComptableId)
    {
        $this->controlerStatusExerciceComptable($exerciceComptableId);

        // Vérifier si des écritures sont déjà liées à un décompte
        if (
            Ecriture::where('exercice_comptable_id', '=', $exerciceComptableId)
                ->where('module', '=', self::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL)
                ->whereNotNull('decompte_id')
                ->exists()
        ) {
            throw new ArrayException([], 'Des écritures sont déjà facturées dans un décompte.');
        }

        // Suppression des écritures
        Ecriture::where('exercice_comptable_id', '=', $exerciceComptableId)
            ->where('module', '=', self::ECRITURE_MODULE_FRAIS_INDEMNITE_ANNUEL)
            ->whereNull('decompte_id')
            ->delete();

        return true;
    }

    /**
     * Générer les écritures liés aux présences des sapeurs durant cette intervention
     */
    public function imputerIntervention($interventionId, $data)
    {
        $intervention = $this->interventionRepo->findWith($interventionId, ['presences', 'phases', 'localite', 'typeIntervention']);
        if ($intervention === null) {
            throw new ArrayException([], "Intervention introuvable.");
        }

        $this->controlerStatusExerciceComptable($intervention->exercice_comptable_id);

        $indemniteType = IndemniteInterventionType::with('fonctions')->find($data['indemnite_intervention_type_id']);

        if ($intervention->statut !== InterventionBusiness::INTERVENTION_STATUT_VALIDE) {
            throw new ArrayException(array("message" => "Impossible d'imputer cette intervention"));
        }

        if ($indemniteType === null) {
            throw new ArrayException([], "Type d'indemnité introuvable");
        }

        $ecritures = [];
        if ($indemniteType->taux_weekend > 0 || $indemniteType->taux_nuit > 0) {
            $ecritures = $this->imputerInterventionTaux($intervention, $indemniteType);
        } else {
            $ecritures = $this->imputerInterventionTarifMin($intervention, $indemniteType);
        }
        Ecriture::insert($ecritures);

        // Update statut
        return $this->interventionRepo->editInterventionInformationsById($interventionId, [
            "statut" => InterventionBusiness::INTERVENTION_STATUT_IMPUTE
        ])->statut;
    }

    /**
     * Générer les écritures liés aux présences des sapeurs durant cette intervention
     * 
     * Décompose le temps de chaque sapeurs entre les différentes phases et applique le tarif minimum
     */
    private function imputerInterventionTarifMin($intervention, $indemniteType)
    {
        // Grouper les présences par sapeurs
        $sapeurs = [];
        foreach ($intervention->presences as $presence) {
            if (!array_key_exists($presence->sapeur_id, $sapeurs)) {
                $sapeurs[$presence->sapeur_id] = [];
            }
            $sapeurs[$presence->sapeur_id][] = $presence;
        }

        $phases = collect($intervention->phases)->sortByDesc('debut');

        $dureeTarifMin = [];
        $dureeNonTarifMin = [];

        $indemnite_phase_id = $indemniteType->phase_id;

        // Sépare les période de chaque sapeur entre les différentes phases
        foreach ($sapeurs as $sapeurId => $presences) {
            $dureeTarifMinSapeur = 0;
            $dureeNonTarifMinSapeur = 0;
            foreach ($presences as $periode) {
                $debut = Carbon::parse($periode->debut);
                $fin = Carbon::parse($periode->fin);

                // Parcourir toutes les phases pour découper la période complète
                foreach ($phases as $phase) {
                    if ($debut->gte($fin)) {
                        // Plus de temps restant dans cette période
                        break;
                    }

                    $phaseDebut = $phase->debut !== null ? Carbon::parse($phase->debut) : null;

                    // Si la phase commence après la fin de cette période, passer à la phase suivante
                    if ($phaseDebut !== null && $phaseDebut->gte($fin)) {
                        continue;
                    }

                    // Déterminer le début effectif de ce segment
                    $segmentDebut = $phaseDebut !== null ? $phaseDebut->max($debut) : $debut;

                    // Calculer la durée de ce segment
                    $duree = $segmentDebut->diffInMinutes($fin) / 60;

                    // Avancer le pointeur de début pour le prochain segment
                    $fin = $segmentDebut;

                    // Totalité des périodes restantes pour cette phase
                    if ($indemnite_phase_id === null || $indemnite_phase_id === 0 || $phase->phase_type_id === $indemnite_phase_id) {
                        $dureeTarifMinSapeur += $duree;
                    } else {
                        $dureeNonTarifMinSapeur += $duree;
                    }
                }
            }
            $dureeTarifMin[$sapeurId] = $dureeTarifMinSapeur;
            $dureeNonTarifMin[$sapeurId] = $dureeNonTarifMinSapeur;
        }

        // Récupération du type de frais
        $tarif = floatval($indemniteType->tarif);
        $tarifMin = floatval($indemniteType->tarif_min ?? $indemniteType->tarif);
        $tarifMinPour = floatval($indemniteType->tarif_min_pour) ?? 1.0;
        $designation = "{$intervention->localite->designation} ({$intervention->type->designation}) $intervention->lieu";

        $ecritures = [];
        foreach ($dureeTarifMin as $sapeurId => $dureeTarifMinSapeur) {
            // Duree sans tarif min
            $dureeNonTarifMinSapeur = $dureeNonTarifMin[$sapeurId];

            $total = 0;
            $dureeTotal = $dureeNonTarifMinSapeur + $dureeTarifMinSapeur;

            // Calcul du nombre d'heures effectives en tarif min
            if ($dureeTarifMinSapeur > $tarifMinPour) {
                if ($indemniteType->tarif_pro_rata) {
                    $dureeNonTarifMinSapeur += $dureeTarifMinSapeur - $tarifMinPour;
                } else {
                    // Arrondir à l'heure inférieure les heures excédentaires
                    $heuresExcedentaires = $dureeTarifMinSapeur - $tarifMinPour;
                    $dureeNonTarifMinSapeur += floor($heuresExcedentaires);
                }
                $dureeTarifMinSapeur = $tarifMinPour;
            }

            // Calcul du tarif min au pro-rata ou pas dans le cas ou la duree effective est plus petite que la duree min
            if ($indemniteType->tarif_min_pro_rata && $tarifMinPour > 0) {
                $total += $tarifMin / $tarifMinPour * $dureeTarifMinSapeur;
            } else {
                $total += $tarifMin;
            }
            $total += $tarif * $dureeNonTarifMinSapeur;

            $total = self::arrondi_5_centimes($total);

            $ecritures[] = [
                'tarif' => $tarif,
                'tarif_pro_rata' => $indemniteType->tarif_pro_rata,
                'quantite' => $dureeTotal,
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
            ];
        }

        return $ecritures;
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
    private function imputerInterventionTaux($intervention, $indemniteType): array
    {
        $designation = "{$intervention->localite->designation} ({$intervention->type->designation}) $intervention->lieu";

        // Grouper les présences par sapeurs
        $sapeurs = [];
        foreach ($intervention->presences as $presence) {
            if (!array_key_exists($presence->sapeur_id, $sapeurs)) {
                $sapeurs[$presence->sapeur_id] = [];
            }
            array_push($sapeurs[$presence->sapeur_id], $presence);
        }

        $ecritures = [];

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
                $dureeNuit += $debutNuit->diffInHours($finNuit);
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
                $duree = $debut->diffInHours($fin);

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
                    $debutNuit->setDate($debut->year, $debut->month, $debut->day);
                    $finNuit->setDate($debut->year, $debut->month, $debut->day);
                    $nightPeriodOneStart = $debutNuit->copy()->subDay();
                    $nightPeriodOneEnd = $finNuit->copy();
                    $nightPeriodTwoStart = $nightPeriodOneStart->copy()->addDay();
                    $nightPeriodTwoEnd = $nightPeriodOneEnd->copy()->addDay();

                    if ($debutCarbon->copy()->subDay() == $finCarbon) {
                        // Debut et fin la même journée
                        if ($debut->isWeekend() && $testWeekend) {
                            $dureeTarifWeekend += $duree;
                        } elseif ($testNuit) {
                            $overlapping = 0;
                            $overlapping += max($debut->max($nightPeriodOneStart)->diffInHours($fin->min($nightPeriodOneEnd), false), 0.0);
                            $overlapping += max($debut->max($nightPeriodTwoStart)->diffInHours($fin->min($nightPeriodTwoEnd), false), 0.0);
                            $dureeTarifNuit += $overlapping;

                            // if ($presence->sapeur_id == '2')
                            //     dd([$duree, $overlapping, $debut, $fin]);
                            $dureeTarifStandard += $duree - $overlapping;
                        } else {
                            $dureeTarifStandard += $duree;
                        }
                    } else {
                        // Période portant sur deux jours

                        // Modification de la durée
                        $finJour = $debut->copy()->ceilDay();
                        $duree = $debut->diffInHours($finJour);

                        // Premier jour de la présence -> début
                        if ($debut->isWeekend() && $testWeekend) {
                            $dureeTarifWeekend += $duree;
                        } elseif ($testNuit) {
                            $overlapping = 0;

                            // Créer les dates de début et fin de la période 1
                            $overlapping += max($debut->max($nightPeriodOneStart)->diffInHours($finJour->min($nightPeriodOneEnd), false), 0);
                            $overlapping += max($debut->max($nightPeriodTwoStart)->diffInHours($finJour->min($nightPeriodTwoEnd), false), 0);

                            $dureeTarifNuit += $overlapping;
                            $dureeTarifStandard += $duree - $overlapping;
                        } else {
                            $dureeTarifStandard += $duree;
                        }

                        // Deuxième jour de la présence -> fin

                        // Modification de la durée
                        $debutJour = $fin->copy()->floorDay();
                        $duree = $debutJour->diffInHours($fin);

                        if ($fin->isWeekend() && $testWeekend) {
                            $dureeTarifWeekend += $duree;
                        } elseif ($testNuit) {
                            $overlapping = 0;

                            $debutNuit->setDate($fin->year, $fin->month, $fin->day);
                            $finNuit->setDate($fin->year, $fin->month, $fin->day);
                            $nightPeriodOneStart = $debutNuit->copy()->subDay();
                            $nightPeriodOneEnd = $finNuit->copy();
                            $nightPeriodTwoStart = $nightPeriodOneStart->copy()->addDay();
                            $nightPeriodTwoEnd = $nightPeriodOneEnd->copy()->addDay();

                            $overlapping += max($debutJour->max($nightPeriodOneStart)->diffInHours($fin->min($nightPeriodOneEnd), false), 0);
                            $overlapping += max($debutJour->max($nightPeriodTwoStart)->diffInHours($fin->min($nightPeriodTwoEnd), false), 0);

                            $dureeTarifNuit += $overlapping;
                            $dureeTarifStandard += $duree - $overlapping;
                        } else {
                            $dureeTarifStandard += $duree;
                        }
                    }
                }
            }

            // Calcul des totaux
            $totalTarifStandard = self::arrondi_5_centimes($tarif * $dureeTarifStandard);
            $totalTarifNuit = self::arrondi_5_centimes($tarif * $dureeTarifNuit * $tauxNuit);
            $totalTarifWeekend = self::arrondi_5_centimes($tarif * $dureeTarifWeekend * $tauxWeekend);

            // Génération des écritures
            if ($totalTarifStandard > 0) {
                $ecritures[] = [
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
                    'intervention_id' => $intervention->id,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,

                    'decompte_id' => null,

                    'module' => self::ECRITURE_MODULE_INTERVENTION,
                    'type' => $indemniteType->type,
                ];
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
                    'intervention_id' => $intervention->id,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,

                    'decompte_id' => null,

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
                    'intervention_id' => $intervention->id,
                    'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,

                    'decompte_id' => null,

                    'module' => self::ECRITURE_MODULE_INTERVENTION,
                    'type' => $indemniteType->type,
                ];
            }
        }

        return $ecritures;
    }

    public function imputerExercice($exerciceId, $data)
    {
        $exercice = Exercice::with(['sapeurs', 'localite'])->findOrFail($exerciceId);
        if (!$exercice) {
            throw new ArrayException([], "Exercice introuvable.");
        }

        $this->controlerStatusExerciceComptable($exercice->exercice_comptable_id);

        if ($exercice->statut !== ExerciceBusiness::EXERCICE_STATUT_VALIDE) {
            throw new ArrayException([], "Impossible d'imputer cet exercice");
        }

        $indemniteType = IndemniteExerciceType::with('fonctions')->find($data['indemnite_exercice_type_id']);
        if (!$indemniteType) {
            throw new ArrayException([], "Type d'indemnité introuvable");
        }

        $unite = $indemniteType->type_unite_id;
        $designation = "{$exercice->localite->designation} ({$exercice->lieu}) $exercice->designation";
        $sapeurs = collect($exercice->sapeurs)->filter(function ($sap) {
            return $sap->present;
        })->values()->all();

        if ($unite === self::UNITE_PIECE || $unite === self::UNITE_FORFAIT) {
            $this->imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation);
        } else if ($unite === self::UNITE_HEURE) {
            $this->imputerExerciceParHeure($exercice, $sapeurs, $indemniteType, $designation);
        } else {
            throw new ArrayException([], "Unité non supportée");
        }

        // Imputation heure supp
        $heures = HeureExercice::where('exercice_id', $exerciceId)->get();
        $this->imputerExerciceHeureSup($exercice, $heures, $designation);

        // Changer le statut de l'exercice
        return $this->exerciceRepo->updateExerciceById($exerciceId, ["statut" => ExerciceBusiness::EXERCICE_STATUT_IMPUTE])->statut;
    }

    private function imputerExerciceHeureSup($exercice, $heures, $designation)
    {
        if ($heures->isEmpty()) {
            return;
        }

        // Charger uniquement les types nécessaires
        $typeIds = $heures->pluck('heure_exercice_type_id')->unique();
        $heureTypes = HeureExerciceType::whereIn('id', $typeIds)->get();
        $indexedTypes = $heureTypes->keyBy('id');

        // Construire le tableau pour l'insertion par lot
        $ecritures = [];
        foreach ($heures as $heure) {
            $tarifType = $indexedTypes->get($heure->heure_exercice_type_id);

            if (!$tarifType) {
                throw new ArrayException([], "Type d'heure exercice introuvable");
            }

            $designationSapeur = $designation . " - " . $heure->designation;
            $total = $heure->quantite * $tarifType->montant;

            // Par heure -> calcul de la durée
            $ecritures[] = [
                'tarif' => $tarifType->montant,
                'quantite' => $heure->quantite,
                'tarif_min' => null,
                'tarif_min_pour' => null,
                'total' => $total,

                'designation' => $designationSapeur,
                'type_unite_id' => $tarifType->type_unite_id,
                'sapeur_id' => $heure->sapeur_id,
                'compte_id' => $tarifType->compte_id,
                'exercice_comptable_id' => $exercice->exercice_comptable_id,
                'exercice_id' => $exercice->id,
                'ecriture_categorie_id' => $tarifType->ecriture_categorie_id,
                'date' => $exercice->date,
                'heure' => $exercice->heure,

                'module' => self::ECRITURE_MODULE_EXERCICE,
                'type' => $tarifType->type,
            ];
        }

        if (!empty($ecritures)) {
            Ecriture::insert($ecritures);
        }
    }

    private function imputerExerciceParPiece($exercice, $sapeurs, $indemniteType, $designation)
    {
        if (empty($sapeurs)) {
            return;
        }

        // Charger tous les détails des sapeurs en amont pour éviter les requêtes N+1
        $sapeurIds = array_map(fn($s) => $s->sapeur_id, $sapeurs);
        $sapeursDetails = [];
        foreach ($sapeurIds as $sapeurId) {
            $sapeursDetails[$sapeurId] = $this->sapeurRepo->getSapeurDetailsById($sapeurId);
        }

        // Prétraiter le mapping des tarifs par fonction pour éviter les filtrages répétés
        $tarifsByFonction = [];
        $defaultTarifs = [];
        foreach ($indemniteType->fonctions as $fonction) {
            if ($fonction->fonction_id === null) {
                $defaultTarifs[] = $fonction;
            } else {
                $tarifsByFonction[$fonction->fonction_id][] = $fonction;
            }
        }

        $ecritures = [];
        foreach ($sapeurs as $sapeur) {
            $sapeurDetails = $sapeursDetails[$sapeur->sapeur_id] ?? null;
            if (!$sapeurDetails) {
                continue;
            }

            $fonction_tarifs = $tarifsByFonction[$sapeurDetails->fonction_id] ?? $defaultTarifs;

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

        if (!empty($ecritures)) {
            Ecriture::insert($ecritures);
        }
    }

    private function imputerExerciceParHeure($exercice, $sapeurs, $indemniteType, $designation)
    {
        if (empty($sapeurs)) {
            return;
        }

        $duree = $exercice->duree / 60;

        // Charger tous les détails des sapeurs en amont pour éviter les requêtes N+1
        $sapeurIds = array_map(fn($s) => $s->sapeur_id, $sapeurs);
        $sapeursDetails = [];
        foreach ($sapeurIds as $sapeurId) {
            $sapeursDetails[$sapeurId] = $this->sapeurRepo->getSapeurDetailsById($sapeurId);
        }

        // Prétraiter le mapping des tarifs par fonction pour éviter les filtrages répétés
        $tarifsByFonction = [];
        $defaultTarifs = [];
        foreach ($indemniteType->fonctions as $fonction) {
            if ($fonction->fonction_id === null) {
                $defaultTarifs[] = $fonction;
            } else {
                $tarifsByFonction[$fonction->fonction_id][] = $fonction;
            }
        }

        $ecritures = [];
        foreach ($sapeurs as $sapeur) {
            $sapeurDetails = $sapeursDetails[$sapeur->sapeur_id] ?? null;
            if (!$sapeurDetails) {
                continue;
            }

            $fonction_tarifs = $tarifsByFonction[$sapeurDetails->fonction_id] ?? $defaultTarifs;

            foreach ($fonction_tarifs as $indemnite) {
                $total = $indemnite->tarif * $duree;

                // Par heure -> calcul de la durée
                $ecritures[] = [
                    'tarif' => $indemnite->tarif,
                    'quantite' => $duree,
                    'tarif_min' => null,
                    'tarif_min_pour' => null,
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
                ];
            }
        }

        if (!empty($ecritures)) {
            Ecriture::insert($ecritures);
        }
    }

    /**
     * Génères des frais annuels pour les sapeurs n'ayant pas encore de frais annuels
     */
    public function imputerCours(int $coursSapeurId, $data)
    {
        // Validate required data keys
        if (!isset($data['exercice_comptable_id']) || !isset($data['indemnite_cours_type_id'])) {
            throw new ArrayException([], "Données requises manquantes");
        }

        $this->controlerStatusExerciceComptable($data['exercice_comptable_id']);

        // Chargement du cours
        $cours = CoursSapeur::with(['cours', 'ecritures'])->find($coursSapeurId);
        if ($cours === null) {
            throw new ArrayException([], "Cours introuvable");
        }

        // Vérifier que le cours n'est pas déjà imputé
        if (!$cours->ecritures->isEmpty()) {
            throw new ArrayException([], "Cours déjà imputé");
        }

        // Chargement de l'indemnité type
        $indemniteType = IndemniteCoursType::with('fonctions')->find($data['indemnite_cours_type_id']);
        if ($indemniteType === null) {
            throw new ArrayException([], "Indemnité type invalide");
        }

        if ($indemniteType->fonctions->isEmpty()) {
            throw new ArrayException([], "Aucune fonction configurée pour ce type d'indemnité");
        }

        $ecritures = [];
        $designation = "Cours " . $cours->cours->designation;

        foreach ($indemniteType->fonctions as $fonction) {
            $ecriture = [
                'compte_id' => $fonction->compte_id,
                'designation' => $designation,
                'type_unite_id' => $fonction->type_unite_id,
                'sapeur_id' => $cours->sapeur_id,
                'cours_sapeur_id' => $coursSapeurId,
                'exercice_comptable_id' => $data['exercice_comptable_id'],
                'ecriture_categorie_id' => $indemniteType->ecriture_categorie_id,
                'date' => $cours->date,
                'heure' => '',
                'module' => self::ECRITURE_MODULE_COURS,
                'type' => $fonction->type,
            ];

            switch ($fonction->type_unite_id) {
                case self::UNITE_JOUR:
                    $ecriture['tarif'] = $fonction->tarif;
                    $ecriture['quantite'] = $cours->duree;
                    $ecriture['total'] = $cours->duree * $fonction->tarif;
                    break;

                case self::UNITE_FORFAIT:
                case self::UNITE_PIECE:
                    $ecriture['tarif'] = $fonction->tarif;
                    $ecriture['quantite'] = 1.0;
                    $ecriture['total'] = $fonction->tarif;
                    break;

                default:
                    throw new ArrayException([], "Unité de l'indemnité type invalide");
            }

            $ecritures[] = $ecriture;
        }

        if (!empty($ecritures)) {
            Ecriture::insert($ecritures);
        }
        return $ecritures;
    }

    public function annulerImputationCours(int $coursSapeurId)
    {
        // Charger toutes les écritures pour ce cours en une seule requête
        $ecritures = Ecriture::where('cours_sapeur_id', $coursSapeurId)->get();

        if ($ecritures->isEmpty()) {
            throw new ArrayException([], 'Aucune écriture trouvée pour ce cours');
        }

        // Utiliser la première écriture pour vérifier le statut de l'exercice comptable
        $this->controlerStatusExerciceComptable($ecritures->first()->exercice_comptable_id);

        // Vérifier si une écriture est déjà liée à un décompte
        if ($ecritures->contains(fn($e) => $e->decompte_id !== null)) {
            throw new ArrayException([], 'Des écritures sont déjà facturées dans un décompte.');
        }

        // Suppression des écritures
        Ecriture::where('cours_sapeur_id', $coursSapeurId)->delete();

        return true;
    }

    /**
     * Imputation de travaux
     */
    public function imputerTravaux($ids)
    {
        $travaux = Travail::whereIn('id', $ids)
            ->where('statut', TravauxBusiness::TRAVAIL_STATUT_VALIDE)
            ->get();

        if ($travaux->isEmpty()) {
            return [];
        }

        // Vérifier que tous les exercices comptables ne sont pas cloturés
        $exercicesComptablesIds = $travaux->pluck('exercice_comptable_id')->unique();
        foreach ($exercicesComptablesIds as $exerciceComptableId) {
            $this->controlerStatusExerciceComptable($exerciceComptableId);
        }

        // Chargement des types de travaux - charger uniquement les types nécessaires
        $travailTypeIds = $travaux->pluck('travail_type_id')->unique();
        $types = TravailType::with(['fonctions'])
            ->whereIn('id', $travailTypeIds)
            ->get();
        $indexedTypes = $types->keyBy('id');

        $ecritures = [];

        foreach ($travaux as $travail) {
            $type = $indexedTypes->get($travail->travail_type_id);

            if (!$type) {
                throw new ArrayException([], "Type de travail introuvable pour le travail ID {$travail->id}");
            }

            foreach ($type->fonctions as $fonction) {
                $ecritures[] = [
                    'tarif' => $fonction->tarif,
                    'quantite' => $travail->quantite,
                    'total' => $travail->quantite * $fonction->tarif,
                    'compte_id' => $fonction->compte_id,
                    'designation' => $type->designation . " - " . $travail->designation,
                    'type_unite_id' => $type->type_unite_id,
                    'sapeur_id' => $travail->sapeur_id,
                    'travail_id' => $travail->id,
                    'exercice_comptable_id' => $travail->exercice_comptable_id,
                    'ecriture_categorie_id' => $type->ecriture_categorie_id,
                    'date' => $travail->date,
                    'heure' => '',
                    'module' => self::ECRITURE_MODULE_FICHE_TRAVAIL,
                    'type' => $fonction->type,
                ];
            }
        }

        if (!empty($ecritures)) {
            Ecriture::insert($ecritures);
        }

        Travail::whereIn('id', $ids)
            ->where('statut', TravauxBusiness::TRAVAIL_STATUT_VALIDE)
            ->update(['statut' => TravauxBusiness::TRAVAIL_STATUT_IMPUTE]);

        return $ecritures;
    }

    public function annulerImputationTravail($travailId)
    {
        $travail = Travail::find($travailId);

        if (!$travail) {
            throw new ArrayException([], 'Travail introuvable');
        }

        $this->controlerStatusExerciceComptable($travail->exercice_comptable_id);

        // Vérifier si des écritures sont déjà liées à un décompte
        if (
            Ecriture::where('travail_id', $travailId)
                ->whereNotNull('decompte_id')
                ->exists()
        ) {
            throw new ArrayException([], 'Des écritures sont déjà facturées dans un décompte.');
        }

        // Suppression des écritures
        Ecriture::where('travail_id', $travailId)->delete();

        Travail::where('id', $travailId)
            ->update(['statut' => TravauxBusiness::TRAVAIL_STATUT_VALIDE]);

        return ['statut' => TravauxBusiness::TRAVAIL_STATUT_VALIDE];
    }
}
