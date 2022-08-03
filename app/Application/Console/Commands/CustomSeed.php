<?php

namespace App\Application\Console\Commands;

use App\Infrastructure\Models\Cours;
use App\Infrastructure\Models\Localite;
use App\Infrastructure\Models\TypeUnite;
use Illuminate\Console\Command;

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
            printf("DATABASE " . $db . "\n");
            Cours::where('designation', '=', 'Chef d\'intervention 2')->update(['duree' => 5]);
            Cours::where('designation', '=', 'Chef d\'intervention 1')->update(['duree' => 5]);
            Cours::where('designation', '=', 'Défense hydrocarbure')->update(['duree' => 5]);
            Cours::where('designation', '=', 'Chef de groupe')->update(['validite_debut' => '2017-01-01', 'duree' => 5]);
            Cours::where('designation', '=', 'CG échelles remorquables')->update(['validite_fin' => '2012-12-31']);
            Cours::where('designation', '=', 'Electricien')->update(['validite_fin' => '2012-12-31']);
            Cours::where('designation', '=', 'Porteur')->update(['validite_fin' => '2014-12-31']);
            Cours::where('designation', '=', 'Garde et circulation')->update(['validite_fin' => '2010-12-31']);
            Cours::where('designation', '=', 'Rattrapage')->update(['validite_fin' => '2020-12-31']);
            Cours::where('designation', '=', 'Porteur')->update(['validite_fin' => '2014-12-31']);
            Cours::where('designation', '=', 'FTB (formation technique de base)')->update(['validite_debut' => '2015-01-01', 'duree' => 3]);
            Cours::where('designation', '=', 'FGB/PR (formation général de base+PR)')->update(['validite_debut' => '2015-01-01', 'duree' => 5]);
            Cours::where('designation', '=', 'BLS-AED')->update(['duree' => 0.5]);
            Cours::where('designation', '=', 'Machiniste')->update(['duree' => 5]);
            Cours::where('designation', '=', 'Chef de groupe 1')->update(['validite_fin' => '2016-12-31']);
            Cours::where('designation', '=', 'Chef de groupe 2')->update(['validite_fin' => '2016-12-31']);
            Cours::where('designation', '=', 'Cours de base')->update(['validite_fin' => '2014-12-31']);
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
