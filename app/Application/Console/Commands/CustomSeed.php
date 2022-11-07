<?php

namespace App\Application\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CustomSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbs:seed-custom';

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
            Artisan::call('db:seed --database=' . $db);

            // printf("DATABASE " . $db . "\n");
            // Cours::on($db)->where('abreviation', '=', 'CI 2')->update(['duree' => 5]);
            // Cours::on($db)->where('abreviation', '=', 'CI 1')->update(['duree' => 5]);
            // Cours::on($db)->where('abreviation', '=', 'DCH')->update(['duree' => 5]);
            // Cours::on($db)->where('designation', '=', 'Chef de groupe')->update(['validite_debut' => '2017-01-01', 'duree' => 5]);
            // Cours::on($db)->where('designation', '=', 'CG échelles remorquables')->update(['validite_fin' => '2012-12-31']);
            // Cours::on($db)->where('designation', '=', 'Electricien')->update(['validite_fin' => '2012-12-31']);
            // Cours::on($db)->where('designation', '=', 'Porteur')->update(['validite_fin' => '2014-12-31']);
            // Cours::on($db)->where('designation', '=', 'Garde et circulation')->update(['validite_fin' => '2010-12-31']);
            // Cours::on($db)->where('designation', '=', 'Rattrapage')->update(['validite_fin' => '2020-12-31']);
            // Cours::on($db)->where('designation', '=', 'Porteur')->update(['validite_fin' => '2014-12-31']);
            // Cours::on($db)->where('abreviation', '=', 'BA 1')->update(['validite_debut' => '2015-01-01', 'duree' => 5]);
            // Cours::on($db)->where('abreviation', '=', 'BA 2')->update(['validite_debut' => '2015-01-01', 'duree' => 3]);
            // Cours::on($db)->where('designation', '=', 'BLS-AED')->update(['duree' => 0.5]);
            // Cours::on($db)->where('designation', '=', 'Machiniste')->update(['duree' => 5]);
            // Cours::on($db)->where('designation', '=', 'Chef de groupe 1')->update(['validite_fin' => '2016-12-31']);
            // Cours::on($db)->where('designation', '=', 'Chef de groupe 2')->update(['validite_fin' => '2016-12-31']);
            // Cours::on($db)->where('abreviation', '=', 'BA')->update(['validite_fin' => '2014-12-31']);
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
