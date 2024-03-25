<?php

namespace App\Console\Commands;

use App\Domaine\Business\ImputationBusiness;
use App\Infrastructure\Models\AvsParam;
use App\Infrastructure\Models\Commune;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Fonction;
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
            Commune::on($db)->insert([
                array('id' => '70', 'designation' => 'Lajoux'),
                array('id' => '71', 'designation' => 'Les Genevez'),
                array('id' => '72', 'designation' => 'Fahy'),
                array('id' => '73', 'designation' => 'Courtedoux'),
                array('id' => '74', 'designation' => 'Les Enfers'),
                array('id' => '75', 'designation' => 'St-Brais'),
            ]);

            Localite::on($db)->where('id', '=', 47)->update([
                'commune_id' => '70', 'npa' => '2718', 'designation' => 'Lajoux'
            ]);
            Localite::on($db)->where('id', '=', 48)->update([
                'commune_id' => '37', 'npa' => '2360', 'designation' => 'Le Bémont'
            ]);
            Localite::on($db)->where('id', '=', 58)->update([
                'commune_id' => '71', 'npa' => '2714', 'designation' => 'Les Genevez'
            ]);
            Localite::on($db)->where('id', '=', 39)->update([
                'commune_id' => '72', 'npa' => '2916', 'designation' => 'Fahy'
            ]);
            Localite::on($db)->where('id', '=', 27)->update([
                'commune_id' => '73', 'npa' => '2905', 'designation' => 'Courtedoux'
            ]);
            Localite::on($db)->where('id', '=', 41)->update([
                'commune_id' => '70', 'npa' => '2718', 'designation' => 'Fornet-Dessus'
            ]);
            Localite::on($db)->where('id', '=', 53)->update([
                'commune_id' => '74', 'npa' => '2363', 'designation' => 'Les Enfers'
            ]);
            Localite::on($db)->where('id', '=', 91)->update([
                'commune_id' => '75', 'npa' => '2364', 'designation' => 'St-Brais'
            ]);
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
