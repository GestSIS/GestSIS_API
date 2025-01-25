<?php

namespace App\Console\Commands;

use App\Domaine\Business\ImputationBusiness;
use App\Infrastructure\Models\AvsParam;
use App\Infrastructure\Models\Commune;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Fonction;
use App\Infrastructure\Models\Localite;
use App\Infrastructure\Models\LocaliteSis;
use App\Infrastructure\Models\Sms;
use App\Infrastructure\Models\SmsNumero;
use App\Infrastructure\Models\ConvocationParam;
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
            ConvocationParam::on("db_" . $db)->insert([
                'title' => 'convocation',
                'affichage_duree' => true,
                'affichage_pour_infor' => true,
            ]);

            // printf("Fix db=db_" . $db . "\n");
            // Commune::on("db_" . $db)->insert([
            //     array('id' => '76', 'designation' => 'Porrentruy'),
            //     array('id' => '77', 'designation' => 'Grand-Fontaine'),
            // ]);

            // Localite::on("db_" . $db)->where('commune_id', '=', 12)->update(['commune_id' => 9]);
            // Localite::on("db_" . $db)->where('commune_id', '=', 13)->update(['commune_id' => 9]);
            // Localite::on("db_" . $db)->where('commune_id', '=', 27)->update(['commune_id' => 26]);

            // Commune::on("db_" . $db)->whereIn('id', [12, 13, 27])->delete();

            // array('id' => '31', 'commune_id' => '26', 'npa' => '2933', 'designation' => 'Damphreux'),
            // // array('id' => '32', 'commune_id' => NULL, 'npa' => '2933', 'designation' => 'Damphreux-Lugnez'),
            // array('id' => '42', 'commune_id' => '66', 'npa' => '2953', 'designation' => 'Fregiécourt'),
            // // array('id' => '43', 'commune_id' => '66', 'npa' => '2953', 'designation' => 'Fregiécourt-Pleujouse'),

            // LocaliteSis::on("db_" . $db)->whereIn('localite_id', [32, 43])->delete();
            // Localite::on("db_" . $db)->whereIn('id', [32, 43])->delete();

            // Localite::on("db_" . $db)->where('id', '=', 77)->update(['commune_id' => 76]);
            // Localite::on("db_" . $db)->where('id', '=', 46)->update(['commune_id' => 77]);

            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
