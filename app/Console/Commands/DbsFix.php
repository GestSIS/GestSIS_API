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
use Illuminate\Support\Facades\DB;

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

            // Localite::on("db_" . $db)->insert([
            //     ['id' => 153, 'commune_id' => NULL, 'npa' => '2742', 'designation' => 'Perrefitte'],
            //     ['id' => 154, 'commune_id' => NULL, 'npa' => '2762', 'designation' => 'Roches BE'],
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

            // Localite::on("db_" . $db)->whereId(110)->update(['designation' => 'La Chaux-de-Fonds']);
            // Localite::on("db_" . $db)->whereId(46)->update(['commune_id' => 77]);

            // Fix 2026-07: avs_total/ac_total ne comptabilisaient que la part employé
            // de la charge AVS/AC (au lieu de la charge complète part employé +
            // employeur), et `total` ne rajoutait que cette même moitié (au lieu de la
            // charge complète). Voir PaiementBusiness::creerDecompteInterne.
            //
            // Idempotent sans migration ni colonne supplémentaire : Paiement.avs_ac
            // (part employé, avs+ac combinés) n'est touché ni par le bug ni par ce
            // correctif — c'est une référence stable. Si avs_total+ac_total du
            // décompte ≈ Σ Paiement.avs_ac, il n'a pas encore été corrigé (une seule
            // part) ; si ≈ 2×Σ Paiement.avs_ac, il l'a déjà été → on saute.
            $fixed = 0;
            $skipped = 0;
            DB::connection("db_" . $db)->transaction(function () use ($db, &$fixed, &$skipped) {
                foreach (
                    Decompte::on("db_" . $db)->where('deduction', true)
                        ->with('paiements')->cursor() as $decompte
                ) {
                    $sumAvsAc = (float) $decompte->paiements->sum('avs_ac');
                    $currentCharge = (float) $decompte->avs_total + (float) $decompte->ac_total;

                    if ($sumAvsAc <= 0.0 || abs($currentCharge - $sumAvsAc) > 0.01) {
                        // Pas de charge AVS/AC sur ce décompte, ou déjà à ~2x (corrigé)
                        $skipped++;
                        continue;
                    }

                    $decompte->total += $decompte->avs_total + $decompte->ac_total;
                    $decompte->avs_total *= 2;
                    $decompte->ac_total *= 2;
                    $decompte->save();
                    $fixed++;
                }
            });
            printf("  %d décompte(s) corrigé(s), %d ignoré(s) (déjà corrects)\n", $fixed, $skipped);

            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
