<?php

namespace App\Console\Commands;

use App\Domaine\Business\SapeurBusiness;
use App\Support\Sis;
use Illuminate\Console\Command;

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
    protected $description = 'Recalcule les statuts sapeur et civil, la fonction principale et le grade principal';

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
        Sis::each(function ($db) {
            printf("Recompute for sis=" . $db . "\n");
            SapeurBusiness::recomputeSapeurStatuts();
            SapeurBusiness::recomputeSapeurFonctionPrincipale();
            SapeurBusiness::recomputeSapeurGradePrincipal();

            printf("\n");
        });
        printf("Migrating done\n");
        return 0;
    }
}
