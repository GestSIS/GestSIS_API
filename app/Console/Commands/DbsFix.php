<?php

namespace App\Console\Commands;

use App\Domaine\Business\ImputationBusiness;
use App\Domaine\Business\Materiel\MaterielTypeBusiness;
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
use App\Support\Sis;
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
        Sis::each(function ($db) {
            printf("Fix db=" . Sis::connection($db) . "\n");

            // $typesIds = MaterielEventType::find(1)?->materielTypeIds() ?? [];//->materielTypeIds;
            // MaterielType::whereIn('id', $typesIds)->update(['est_lavable' => True, 'est_numerote' => True]);

            // // Attribut est_taillee
            // MaterielType::whereIn(
            //     'id',
            //     Article::where('taille', '!=', "")
            //         ->where("taille", "!=", "-")
            //         ->distinct('materiel_type_id')
            //         ->pluck('materiel_type_id')
            // )->update(['est_taillee' => True]);


            // // Attribut est_numerotee
            // MaterielType::whereIn(
            //     'id',
            //     Article::where('numero', '!=', "")
            //         ->where("numero", "!=", "-")
            //         ->distinct('materiel_type_id')
            //         ->pluck('materiel_type_id')
            // )->update(['est_numerote' => True]);

            // ConvocationParam::insert([
            //     'titre' => 'convocation',
            //     'affichage_duree' => true,
            //     'affichage_pour_infor' => true,
            // ]);

            // Localite::insert([
            //     ['id' => 153, 'commune_id' => NULL, 'npa' => '2742', 'designation' => 'Perrefitte'],
            //     ['id' => 154, 'commune_id' => NULL, 'npa' => '2762', 'designation' => 'Roches BE'],
            // ]);

            // Localite::where('commune_id', '=', 12)->update(['commune_id' => 9]);
            // Localite::where('commune_id', '=', 13)->update(['commune_id' => 9]);
            // Localite::where('commune_id', '=', 27)->update(['commune_id' => 26]);

            // Commune::whereIn('id', [12, 13, 27])->delete();

            // array('id' => '31', 'commune_id' => '26', 'npa' => '2933', 'designation' => 'Damphreux'),
            // // array('id' => '32', 'commune_id' => NULL, 'npa' => '2933', 'designation' => 'Damphreux-Lugnez'),
            // array('id' => '42', 'commune_id' => '66', 'npa' => '2953', 'designation' => 'Fregiécourt'),
            // // array('id' => '43', 'commune_id' => '66', 'npa' => '2953', 'designation' => 'Fregiécourt-Pleujouse'),

            // LocaliteSis::whereIn('localite_id', [32, 43])->delete();
            // Localite::whereIn('id', [32, 43])->delete();

            // Localite::whereId(110)->update(['designation' => 'La Chaux-de-Fonds']);
            // Localite::whereId(46)->update(['commune_id' => 77]);

            // Fix 2026-07: avs_total/ac_total ne comptabilisaient que la part employé
            // de la côtisation AVS/AC (au lieu de la côtisation complète part employé +
            // employeur), et `total` ne rajoutait que cette même moitié (au lieu de la
            // côtisation complète). Voir PaiementBusiness::creerDecompteInterne.
            //
            // Idempotent sans migration ni colonne supplémentaire : Paiement.avs_ac
            // (part employé, avs+ac combinés) n'est touché ni par le bug ni par ce
            // correctif — c'est une référence stable. Si avs_total+ac_total du
            // décompte ≈ Σ Paiement.avs_ac, il n'a pas encore été corrigé (une seule
            // part) ; si ≈ 2×Σ Paiement.avs_ac, il l'a déjà été → on saute.
            // $fixed = 0;
            // $skipped = 0;
            // DB::transaction(function () use (&$fixed, &$skipped) {
            //     foreach (
            //         Decompte::where('deduction', true)
            //             ->with('paiements')->cursor() as $decompte
            //     ) {
            //         $sumAvsAc = (float) $decompte->paiements->sum('avs_ac');
            //         $currentCharge = (float) $decompte->avs_total + (float) $decompte->ac_total;

            //         if ($sumAvsAc <= 0.0 || abs($currentCharge - $sumAvsAc) > 0.01) {
            //             // Pas de côtisations AVS/AC sur ce décompte, ou déjà à ~2x (corrigé)
            //             $skipped++;
            //             continue;
            //         }

            //         $decompte->total += $decompte->avs_total + $decompte->ac_total;
            //         $decompte->avs_total *= 2;
            //         $decompte->ac_total *= 2;
            //         $decompte->save();
            //         $fixed++;
            //     }
            // });
            // printf("  %d décompte(s) corrigé(s), %d ignoré(s) (déjà corrects)\n", $fixed, $skipped);

            // Fix 2026-08: est_emplacement n'est dérivé automatiquement de `type` que
            // par MaterielTypeBusiness::createProduct/editProduct (nouveaux types ou
            // types modifiés depuis l'ajout de la colonne) ; les materiel_types
            // véhicule existants créés avant restent à false. Idempotent.
            // $typesVehiculeCorriges = MaterielType::where('type', MaterielTypeBusiness::TYPE_VEHICULE)
            //     ->where('est_emplacement', false)
            //     ->update(['est_emplacement' => true]);
            // printf("  %d materiel_type(s) véhicule corrigé(s) (est_emplacement)\n", $typesVehiculeCorriges);

            // Fix 2026-08: ajout de la civilité "Non-binaire" (idempotent via insertOrIgnore,
            // id fixe pour rester cohérent avec le seeder CiviliteTableSeeder).
            DB::table('civilites')->insertOrIgnore([
                'id' => 3,
                'designation' => 'Non-binaire',
                'forme_politesse' => '',
            ]);
            printf("  civilité 'Non-binaire' vérifiée/ajoutée\n");

            printf("\n");
        });
        printf("Migrating done\n");
        return 0;
    }
}
