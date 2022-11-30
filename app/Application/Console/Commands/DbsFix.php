<?php

namespace App\Application\Console\Commands;

use App\Domaine\Business\ImputationBusiness;
use App\Infrastructure\Models\Ecriture;
use Illuminate\Console\Command;

class DbsFixEcrituresAvs extends Command
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
            foreach (Ecriture::on($db)->where('module', '=', ImputationBusiness::ECRITURE_MODULE_AVS)->get() as $ecriture) {
                Ecriture::on($db)->where('id', $ecriture->id)->update(['total' => -$ecriture->total]);
            }
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
