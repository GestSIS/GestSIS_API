<?php

namespace App\Application\Console\Commands;

use App\Domaine\Business\ImputationBusiness;
use App\Infrastructure\Models\AvsParam;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Localite;
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
            Localite::on($db)->insert(array('id' => '149', 'commune_id' => NULL, 'npa' => '2827', 'designation' => 'La Scheulte'));
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
