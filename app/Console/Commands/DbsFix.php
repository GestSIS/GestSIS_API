<?php

namespace App\Console\Commands;

use App\Domaine\Business\ImputationBusiness;
use App\Models\Article;
use App\Models\AvsParam;
use App\Models\Commune;
use App\Models\Decompte;
use App\Models\Ecriture;
use App\Models\Fonction;
use App\Models\Localite;
use App\Models\LocaliteSis;
use App\Models\Sms;
use App\Models\SmsNumero;
use App\Models\ConvocationParam;
use App\Models\MaterielEventType;
use App\Models\MaterielType;
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
            printf("Fix db=db_" . $db . "\n");

            // $typesIds = MaterielEventType::on("db_" . $db)->find(1)?->materielTypeIds() ?? [];//->materielTypeIds;
            // MaterielType::on("db_" . $db)->whereIn('id', $typesIds)->update(['est_lavable' => True, 'est_numerote' => True]);

            // // Attribut est_taillee
            // MaterielType::on("db_" . $db)
            //     ->whereIn(
            //         'id',
            //         Article::on("db_" . $db)->where('taille', '!=', "")
            //             ->where("taille", "!=", "-")
            //             ->distinct('materiel_type_id')
            //             ->pluck('materiel_type_id')
            //     )->update(['est_taillee' => True]);


            // // Attribut est_numerotee
            // MaterielType::on("db_" . $db)
            //     ->whereIn(
            //         'id',
            //         Article::on("db_" . $db)->where('numero', '!=', "")
            //             ->where("numero", "!=", "-")
            //             ->distinct('materiel_type_id')
            //             ->pluck('materiel_type_id')
            //     )->update(['est_numerote' => True]);

            // ConvocationParam::on("db_" . $db)->insert([
            //     'titre' => 'convocation',
            //     'affichage_duree' => true,
            //     'affichage_pour_infor' => true,
            // ]);

            Localite::on("db_" . $db)->insert([
                ['id' => 153, 'commune_id' => NULL, 'npa' => '2742', 'designation' => 'Perrefitte'],
                ['id' => 154, 'commune_id' => NULL, 'npa' => '2762', 'designation' => 'Roches BE'],
            ]);

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

            // Localite::on("db_" . $db)->where('id', '=', 110)->update(['designation' => 'La Chaux-de-Fonds']);
            // Localite::on("db_" . $db)->where('id', '=', 46)->update(['commune_id' => 77]);

            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
