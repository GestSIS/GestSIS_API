<?php

namespace App\Console\Commands;

use App\Domaine\Business\SapeurBusiness;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class DbsSapeursActifStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbs:sapeurs-actif-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcule le status actif/inactif des sapeurs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dbs = config('database.dbs');
        foreach ($dbs as $db) {
            printf("Recompute for sis=" . $db . "\n");
            Config::set('database.default', 'db_' . $db);
            SapeurBusiness::recomputeSapeurActifStatus();
            SapeurBusiness::recomputeSapeurFonctionPrincipale();
            SapeurBusiness::recomputeSapeurGradePrincipal();

            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
