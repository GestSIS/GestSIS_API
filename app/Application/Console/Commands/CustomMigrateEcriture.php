<?php

namespace App\Application\Console\Commands;

use App\Infrastructure\Models\Ecriture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CustomMigrateEcriture extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbs:migrate-ecritures';

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
            printf("DATABASE " . $db . "\n");
            printf("migrate\n");
            Artisan::call('migrate --database=' . $db);
            printf("migrate ecritures\n");
            Ecriture::on($db)->whereNotNull('exercice_id')->update(['type' => 1]);
            Ecriture::on($db)->whereNotNull('intervention_id')->update(['type' => 2]);
            Ecriture::on($db)->whereNotNull('frais_annuel')->update(['type' => 3]);
            Ecriture::on($db)->whereNotNull('indemnite_annuel')->update(['type' => 4]);
            Ecriture::on($db)->whereNotNull('avs')->update(['type' => 5]);
            Ecriture::on($db)->whereNotNull('amende')->update(['type' => 6]);
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
