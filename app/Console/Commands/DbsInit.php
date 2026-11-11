<?php

namespace App\Console\Commands;

use App\Support\Sis;
use Illuminate\Console\Command;
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class DbsInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbs:init {--sis= : Initialise uniquement ce SIS (utilisé par les processus parallèles)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialises les bases de données (migrate:fresh + seed), un processus par SIS en parallèle';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sis = $this->option('sis');

        if ($sis !== null) {
            return $this->initSis($sis);
        }

        return $this->initAllInParallel();
    }

    /**
     * Lance un processus `dbs:init --sis=<db>` par SIS et attend qu'ils soient tous terminés.
     */
    private function initAllInParallel(): int
    {
        $totalStart = microtime(true);
        $keys = Sis::keys();

        $pool = Process::pool(function (Pool $pool) use ($keys): void {
            foreach ($keys as $db) {
                $pool->as($db)
                    ->path(base_path())
                    ->forever()
                    ->command([PHP_BINARY, 'artisan', 'dbs:init', '--sis=' . $db]);
            }
        })->start(function (string $type, string $output, string $db): void {
            foreach (preg_split('/\R/', rtrim($output)) as $line) {
                printf("[%s] %s\n", $db, $line);
            }
        });

        $results = $pool->wait();

        $failed = $results->collect()->filter->failed()->keys()->all();

        printf("Migrating done in %.2f s\n", microtime(true) - $totalStart);

        if ($failed !== []) {
            $this->error('Échec pour : ' . implode(', ', $failed));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Recrée et seed la base d'un seul SIS, en affichant la durée de chaque phase.
     */
    private function initSis(string $db): int
    {
        if (! Sis::isValid($db)) {
            $this->error("SIS inconnu : {$db}");

            return self::FAILURE;
        }

        Sis::use($db);

        $start = microtime(true);
        $migrateCode = Artisan::call('migrate:fresh');
        printf("migrate:fresh  %6.2f s\n", microtime(true) - $start);

        if ($migrateCode !== self::SUCCESS) {
            echo Artisan::output();

            return self::FAILURE;
        }

        $start = microtime(true);
        $seedCode = Artisan::call('db:seed');
        printf("db:seed        %6.2f s\n", microtime(true) - $start);

        if ($seedCode !== self::SUCCESS) {
            echo Artisan::output();

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
