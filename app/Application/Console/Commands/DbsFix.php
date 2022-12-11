<?php

namespace App\Application\Console\Commands;

use App\Domaine\Business\ImputationBusiness;
use App\Infrastructure\Models\AvsParam;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use Illuminate\Console\Command;

class DbsFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbs:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
            printf("Fix db=" . $db . "\n");
            foreach (Decompte::on($db)->with('paiements')->get() as $decompte) {
                $decompte->a_payer_total = 0.0;
                $decompte->a_facturer_total = 0.0;
                foreach ($decompte->paiements  as $paiement) {
                    if ($paiement->total > 0) {
                        $decompte->a_payer_total += $paiement->total;
                    } else {
                        $decompte->a_facturer_total += $paiement->total;
                    }
                }
                $decompte->save();
            }
            // Ecriture::on($db)->update();
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
