<?php

namespace App\Console\Commands;

use App\Infrastructure\Models\Ecriture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class CorrigerEcrituresBugPhases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interventions:corriger-ecritures-bug-phases 
                            {--sis= : Code du SIS à corriger (ex: hs). Si non spécifié, corrige toutes les bases}
                            {--dry-run : Afficher les écritures qui seraient corrigées sans les modifier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige les écritures de correction bug phases en mettant tarif_min et tarif_min_pour à 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dbs = config('database.dbs');
        $sisFilter = $this->option('sis');
        $dryRun = $this->option('dry-run');

        // Filtrer par SIS si spécifié
        if ($sisFilter) {
            if (!in_array($sisFilter, $dbs)) {
                $this->error("Le SIS '{$sisFilter}' n'existe pas dans la configuration.");
                $this->line("SIS disponibles: " . implode(', ', $dbs));
                return 1;
            }
            $dbs = [$sisFilter];
        }

        $this->info("Correction sur " . count($dbs) . " base(s) de données");
        if ($dryRun) {
            $this->warn("MODE DRY-RUN: Aucune modification ne sera effectuée");
        }
        $this->newLine();

        $totalGlobalEcritures = 0;

        foreach ($dbs as $db) {
            $this->info("=== BASE DE DONNÉES: {$db} ===");
            Config::set('database.default', 'db_' . $db);

            $nbCorrigees = $this->corrigerDatabase($db, $dryRun);
            $totalGlobalEcritures += $nbCorrigees;

            $this->newLine();
        }

        // Rapport global
        $this->info("╔═══════════════════════════════════════════════════════════╗");
        $this->info("║              RAPPORT GLOBAL DE CORRECTION                 ║");
        $this->info("╠═══════════════════════════════════════════════════════════╣");
        $this->info("║ Bases de données traitées:  " . str_pad(count($dbs), 30) . "║");
        $this->info("║ Écritures corrigées:        " . str_pad($totalGlobalEcritures, 30) . "║");
        $this->info("╚═══════════════════════════════════════════════════════════╝");

        return 0;
    }

    /**
     * Corriger les écritures d'une base de données
     */
    private function corrigerDatabase($dbName, $dryRun)
    {
        // Récupérer les écritures de correction bug phases
        $ecritures = Ecriture::where('designation', 'LIKE', 'Correction -%')
            ->where(function ($query) {
                $query->where('tarif_min', '!=', 0)
                    ->orWhere('tarif_min_pour', '!=', 0);
            })
            ->get();

        if ($ecritures->isEmpty()) {
            $this->line("  Aucune écriture à corriger trouvée.");
            return 0;
        }

        $this->line("  Écritures à corriger: {$ecritures->count()}");
        $this->newLine();

        // Afficher les détails
        $tableData = [];
        foreach ($ecritures as $ecriture) {
            $tableData[] = [
                $ecriture->id,
                $ecriture->designation,
                $ecriture->sapeur_id,
                $ecriture->tarif_min ?? 'null',
                $ecriture->tarif_min_pour ?? 'null',
                $ecriture->quantite,
                number_format($ecriture->total, 2) . ' CHF'
            ];
        }

        $this->table(
            ['ID', 'Désignation', 'Sapeur', 'Tarif Min', 'Tarif Min Pour', 'Quantité', 'Total'],
            $tableData
        );

        if ($dryRun) {
            $this->warn("  [DRY-RUN] Les {$ecritures->count()} écriture(s) ci-dessus seraient corrigées");
            return $ecritures->count();
        }

        // Demander confirmation
        if (!$this->confirm("Corriger ces {$ecritures->count()} écriture(s) pour {$dbName}?")) {
            $this->line("  Correction annulée.");
            return 0;
        }

        // Effectuer la correction
        $nbCorrigees = 0;
        foreach ($ecritures as $ecriture) {
            $ecriture->tarif_min = 0;
            $ecriture->tarif_min_pour = 0;
            $ecriture->save();
            $nbCorrigees++;
        }

        $this->info("  ✓ {$nbCorrigees} écriture(s) corrigée(s)");

        return $nbCorrigees;
    }
}
