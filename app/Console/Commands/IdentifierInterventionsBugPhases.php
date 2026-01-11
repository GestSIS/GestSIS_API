<?php

namespace App\Console\Commands;

use App\Domaine\Business\InterventionBusiness;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\ExerciceComptable;
use App\Infrastructure\Models\IndemniteInterventionType;
use App\Infrastructure\Models\Intervention;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class IdentifierInterventionsBugPhases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interventions:bug-phases 
                            {--annee= : Année de l\'exercice comptable à analyser (ex: 2024)}
                            {--sis= : Code du SIS à analyser (ex: hs). Si non spécifié, analyse toutes les bases}
                            {--fix : Créer des écritures de correction pour les montants manquants}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Identifie les interventions impactées par le bug des phases (break prématuré) sur toutes les bases de données';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dbs = config('database.dbs');
        $sisFilter = $this->option('sis');

        // Filtrer par SIS si spécifié
        if ($sisFilter) {
            if (!in_array($sisFilter, $dbs)) {
                $this->error("Le SIS '{$sisFilter}' n'existe pas dans la configuration.");
                $this->line("SIS disponibles: " . implode(', ', $dbs));
                return 1;
            }
            $dbs = [$sisFilter];
        }

        $this->info("Analyse sur " . count($dbs) . " base(s) de données");
        $this->newLine();

        $totalGlobalInterventionsAnalysees = 0;
        $totalGlobalInterventionsImpactees = 0;
        $totalGlobalEcartCHF = 0;
        $dbsAvecProblemes = [];

        foreach ($dbs as $db) {
            $this->info("=== BASE DE DONNÉES: {$db} ===");
            Config::set('database.default', 'db_' . $db);

            $result = $this->analyserDatabase($db);

            $totalGlobalInterventionsAnalysees += $result['nb_analysees'];
            $totalGlobalInterventionsImpactees += $result['nb_impactees'];
            $totalGlobalEcartCHF += $result['ecart_total'];

            if ($result['nb_impactees'] > 0) {
                $dbsAvecProblemes[] = [
                    'db' => $db,
                    'nb_impactees' => $result['nb_impactees'],
                    'ecart_chf' => $result['ecart_total']
                ];
            }

            $this->newLine();
        }

        // Rapport global
        $this->info("╔═══════════════════════════════════════════════════════════╗");
        $this->info("║              RAPPORT GLOBAL MULTI-DB                      ║");
        $this->info("╠═══════════════════════════════════════════════════════════╣");
        $this->info("║ Bases de données analysées: " . str_pad(count($dbs), 30) . "║");
        $this->info("║ Interventions analysées:    " . str_pad($totalGlobalInterventionsAnalysees, 30) . "║");
        $this->info("║ Interventions impactées:    " . str_pad($totalGlobalInterventionsImpactees, 30) . "║");
        $this->info("║ Écart total:                " . str_pad(number_format($totalGlobalEcartCHF, 2) . " CHF", 30) . "║");
        $this->info("╚═══════════════════════════════════════════════════════════╝");
        $this->newLine();

        if (!empty($dbsAvecProblemes)) {
            $this->warn("Bases de données avec interventions impactées:");
            $this->table(
                ['Base de données', 'Interventions impactées', 'Écart total (CHF)'],
                array_map(fn($d) => [
                    $d['db'],
                    $d['nb_impactees'],
                    number_format($d['ecart_chf'], 2)
                ], $dbsAvecProblemes)
            );
        }

        return 0;
    }

    /**
     * Analyse une base de données
     */
    private function analyserDatabase($dbName)
    {
        $annee = $this->option('annee');
        $fix = $this->option('fix');

        // Vérifier si le SIS a une config de tarification compatible avec le bug
        // Le bug affecte uniquement les interventions avec tarif_min et phases
        $hasCompatibleConfig = IndemniteInterventionType::whereNotNull('tarif_min')
            ->exists();

        if (!$hasCompatibleConfig) {
            $this->line("  <fg=gray>Aucune configuration tarif_min + phase_id détectée. Skip.</>");
            return [
                'nb_analysees' => 0,
                'nb_impactees' => 0,
                'ecart_total' => 0
            ];
        }

        // Récupérer les interventions imputées avec plusieurs phases
        $query = Intervention::where('statut', InterventionBusiness::INTERVENTION_STATUT_IMPUTE)
            ->with(['presences', 'phases', 'ecritures.sapeur'])
            ->has('phases', '>=', 2);

        if ($annee) {
            // Filtrer par année de l'exercice comptable
            $exerciceComptableIds = ExerciceComptable::where('annee', $annee)->pluck('id');

            if ($exerciceComptableIds->isEmpty()) {
                $this->line("  <fg=gray>Aucun exercice comptable trouvé pour l'année {$annee}. Skip.</>");
                return [
                    'nb_analysees' => 0,
                    'nb_impactees' => 0,
                    'ecart_total' => 0
                ];
            }

            $query->whereIn('exercice_comptable_id', $exerciceComptableIds);
        }

        $interventions = $query->get();

        if ($interventions->isEmpty()) {
            $this->line("  Aucune intervention trouvée avec plusieurs phases.");
            return [
                'nb_analysees' => 0,
                'nb_impactees' => 0,
                'ecart_total' => 0
            ];
        }

        $this->line("  Interventions à analyser: {$interventions->count()}");

        $interventionsImpactees = [];
        $totalEcartCHF = 0;

        foreach ($interventions as $intervention) {
            $ecart = $this->analyserIntervention($intervention);

            if ($ecart['impacte']) {
                $interventionsImpactees[] = $ecart;
                $totalEcartCHF += $ecart['ecart_total_chf'];
            }
        }

        // Afficher le rapport pour cette DB
        $this->line("  Interventions analysées:  {$interventions->count()}");
        $this->line("  Interventions impactées:  " . count($interventionsImpactees));
        $this->line("  Écart total:              " . number_format($totalEcartCHF, 2) . " CHF");

        if (!empty($interventionsImpactees)) {
            $this->newLine();
            $this->table(
                ['ID', 'Date', 'Phases', 'Sapeurs', 'Écart (CHF)', 'Config changée'],
                array_map(fn($i) => [
                    $i['intervention_id'],
                    $i['date'],
                    $i['nb_phases'],
                    $i['nb_sapeurs_impactes'],
                    number_format($i['ecart_total_chf'], 2),
                    $i['config_changee'] ? '⚠ OUI' : 'Non'
                ], $interventionsImpactees)
            );

            // Liste compacte des IDs pour copier-coller
            $ids = array_column($interventionsImpactees, 'intervention_id');
            $this->newLine();
            $this->line("  <fg=cyan>IDs des interventions impactées: " . implode(', ', $ids) . "</>");

            // Option de correction
            if ($fix) {
                $this->newLine();
                if ($this->confirm("Créer les écritures de correction pour {$dbName}?")) {
                    $nbEcritures = $this->fixInterventions($interventionsImpactees);
                    $this->info("  ✓ {$nbEcritures} écriture(s) de correction créée(s)");
                }
            }
        }

        return [
            'nb_analysees' => $interventions->count(),
            'nb_impactees' => count($interventionsImpactees),
            'ecart_total' => $totalEcartCHF
        ];
    }

    /**
     * Analyse une intervention pour détecter l'impact du bug
     */
    private function analyserIntervention($intervention)
    {
        $result = [
            'intervention_id' => $intervention->id,
            'date' => $intervention->date_debut,
            'nb_phases' => $intervention->phases->count(),
            'impacte' => false,
            'nb_sapeurs_impactes' => 0,
            'ecart_total_chf' => 0,
            'config_changee' => false,
            'details' => []
        ];

        // Vérifier que l'intervention a bien été imputée avec tarif_min
        $ecrituresAvecTarifMin = $intervention->ecritures->filter(function ($ecriture) {
            return $ecriture->tarif_min !== null;
        });

        if ($ecrituresAvecTarifMin->isEmpty()) {
            // Cette intervention n'utilise pas le tarif_min, elle n'est pas concernée par ce bug
            return $result;
        }

        // Récupérer la config utilisée lors de l'imputation (depuis la première écriture)
        $ecritureRef = $ecrituresAvecTarifMin->first();
        $configImputee = [
            'tarif' => floatval($ecritureRef->tarif),
            'tarif_min' => floatval($ecritureRef->tarif_min),
            'tarif_min_pour' => floatval($ecritureRef->tarif_min_pour),
            'tarif_min_pro_rata' => boolval($ecritureRef->tarif_min_pro_rata),
            'tarif_pro_rata' => boolval($ecritureRef->tarif_pro_rata),
        ];

        // Récupérer la config actuelle
        $configActuelle = IndemniteInterventionType::whereNotNull('tarif_min')
            ->whereNotNull('phase_id')
            ->first();

        if ($configActuelle) {
            $configActuelleData = [
                'tarif' => floatval($configActuelle->tarif),
                'tarif_min' => floatval($configActuelle->tarif_min),
                'tarif_min_pour' => floatval($configActuelle->tarif_min_pour),
                'tarif_min_pro_rata' => boolval($configActuelle->tarif_min_pro_rata),
                'tarif_pro_rata' => boolval($configActuelle->tarif_pro_rata),
            ];

            // Comparer les configs
            $result['config_changee'] = ($configImputee !== $configActuelleData);
        }

        // Grouper les présences par sapeur
        $sapeurs = [];
        foreach ($intervention->presences as $presence) {
            if (!array_key_exists($presence->sapeur_id, $sapeurs)) {
                $sapeurs[$presence->sapeur_id] = [];
            }
            $sapeurs[$presence->sapeur_id][] = $presence;
        }

        // Trier les phases par date décroissante (comme dans le code original avec le bug)
        $phases = collect($intervention->phases)->sortByDesc('debut');

        // Pour chaque sapeur, calculer la durée avec le bug vs sans le bug
        foreach ($sapeurs as $sapeurId => $presences) {
            $dureeAvecBug = 0;
            $dureeSansBug = 0;

            foreach ($presences as $periode) {
                $debut = Carbon::parse($periode->debut);
                $fin = Carbon::parse($periode->fin);

                // Calcul AVEC le bug (break après première phase)
                $finAvecBug = clone $fin;
                $premierePhaseTrouvee = false;
                foreach ($phases as $phase) {
                    if ($debut->gte($finAvecBug)) {
                        break;
                    }

                    $phaseDebut = $phase->debut != NULL ? Carbon::parse($phase->debut) : null;

                    if ($phaseDebut != NULL && $phaseDebut->gte($finAvecBug)) {
                        continue;
                    }

                    $segmentDebut = $phaseDebut != NULL ? $phaseDebut->max($debut) : $debut;
                    $duree = $segmentDebut->diffInMinutes($finAvecBug) / 60;

                    $dureeAvecBug += $duree;
                    $finAvecBug = $segmentDebut;

                    // BUG: break prématuré
                    $premierePhaseTrouvee = true;
                    break;
                }

                // Calcul SANS le bug (parcourir toutes les phases)
                $finSansBug = clone $fin;
                foreach ($phases as $phase) {
                    if ($debut->gte($finSansBug)) {
                        break;
                    }

                    $phaseDebut = $phase->debut != NULL ? Carbon::parse($phase->debut) : null;

                    if ($phaseDebut != NULL && $phaseDebut->gte($finSansBug)) {
                        continue;
                    }

                    $segmentDebut = $phaseDebut != NULL ? $phaseDebut->max($debut) : $debut;
                    $duree = $segmentDebut->diffInMinutes($finSansBug) / 60;

                    $dureeSansBug += $duree;
                    $finSansBug = $segmentDebut;
                }
            }

            // Vérifier si il y a un écart significatif (> 0.01h = 36 secondes)
            if (abs($dureeAvecBug - $dureeSansBug) > 0.01) {
                // Trouver l'écriture correspondante
                $ecriture = $intervention->ecritures->firstWhere('sapeur_id', $sapeurId);
                $tarifHoraire = $ecriture ? floatval($ecriture->tarif) : 30.0; // Défaut 30 CHF/h

                $ecartHeures = $dureeSansBug - $dureeAvecBug;
                $ecartCHF = $ecartHeures * $tarifHoraire;

                $result['impacte'] = true;
                $result['nb_sapeurs_impactes']++;
                $result['ecart_total_chf'] += $ecartCHF;
                $result['details'][] = [
                    'sapeur_id' => $sapeurId,
                    'sapeur_nom' => $ecriture ? $ecriture->sapeur->nom_prenom : "ID $sapeurId",
                    'duree_avec_bug' => round($dureeAvecBug, 2),
                    'duree_sans_bug' => round($dureeSansBug, 2),
                    'ecart_heures' => round($ecartHeures, 2),
                    'ecart_chf' => round($ecartCHF, 2)
                ];
            }
        }

        return $result;
    }

    /**
     * Créer des écritures de correction pour les interventions impactées
     */
    private function fixInterventions($interventionsImpactees)
    {
        $nbEcritures = 0;

        foreach ($interventionsImpactees as $interventionData) {
            $intervention = Intervention::find($interventionData['intervention_id']);

            foreach ($interventionData['details'] as $detail) {
                // Récupérer une écriture existante pour copier la config
                $ecritureRef = $intervention->ecritures->firstWhere('sapeur_id', $detail['sapeur_id']);

                if (!$ecritureRef) {
                    continue;
                }

                // Créer l'écriture de correction
                $ecriture = new Ecriture([
                    'designation' => "Correction bug phases - " . $ecritureRef->designation,
                    'complement' => "Correction automatique du bug de calcul des phases multiples",
                    'tarif' => $ecritureRef->tarif,
                    'quantite' => $detail['ecart_heures'],
                    'total' => $detail['ecart_chf'],
                    'type_unite_id' => $ecritureRef->type_unite_id,

                    'tarif_min' => $ecritureRef->tarif_min,
                    'tarif_min_pour' => $ecritureRef->tarif_min_pour,
                    'tarif_min_pro_rata' => $ecritureRef->tarif_min_pro_rata,
                    'tarif_pro_rata' => $ecritureRef->tarif_pro_rata,
                    'taux' => $ecritureRef->taux,
                    'taux_description' => $ecritureRef->taux_description,

                    'sapeur_id' => $detail['sapeur_id'],
                    'compte_id' => $ecritureRef->compte_id,
                    'exercice_comptable_id' => $intervention->exercice_comptable_id,
                    'intervention_id' => $intervention->id,
                    'exercice_id' => null,
                    'cours_sapeur_id' => null,
                    'ecriture_categorie_id' => $ecritureRef->ecriture_categorie_id,
                    'decompte_id' => null,

                    'date' => $intervention->date_debut,
                    'heure' => $intervention->heure_debut,

                    'module' => 2, // ECRITURE_MODULE_INTERVENTION
                    'type' => $ecritureRef->type,
                ]);

                $ecriture->save();
                $nbEcritures++;
            }
        }

        return $nbEcritures;
    }
}
