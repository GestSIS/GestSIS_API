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
                            {--annee= : Année de l\'exercice comptable à analyser (ex: 2024)}';

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
        $this->info("║ Bases de données analysées: " . str_pad(count($dbs), 27) . "║");
        $this->info("║ Interventions analysées:    " . str_pad($totalGlobalInterventionsAnalysees, 27) . "║");
        $this->info("║ Interventions impactées:    " . str_pad($totalGlobalInterventionsImpactees, 27) . "║");
        $this->info("║ Écart total:                " . str_pad(number_format($totalGlobalEcartCHF, 2) . " CHF", 27) . "║");
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
                ['ID', 'Date', 'Phases', 'Sapeurs impactés', 'Écart (CHF)'],
                array_map(fn($i) => [
                    $i['intervention_id'],
                    $i['date'],
                    $i['nb_phases'],
                    $i['nb_sapeurs_impactes'],
                    number_format($i['ecart_total_chf'], 2)
                ], $interventionsImpactees)
            );
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
            'details' => []
        ];

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
}
