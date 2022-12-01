<?php

namespace App\Application\Console\Commands;

use App\Domaine\Business\ImputationBusiness;
use App\Infrastructure\Models\AvsParam;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Ecriture;
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
            $avsParam = AvsParam::on($db)->first();
            if ($avsParam) {
                foreach (Decompte::on($db)->get() as $decompte) {
                    $ecritureAvsGlobale = [
                        'tarif' => ($decompte->avs_total + $decompte->ac_total) * 2,
                        'quantite' => 1,
                        'total' => ($decompte->avs_total + $decompte->ac_total) * 2,

                        'designation' => $decompte->designation . " - Charges AVS/AI/APG/AC",
                        'type_unite_id' => ImputationBusiness::UNITE_FORFAIT,
                        'exercice_comptable_id' => $decompte->exercice_comptable_id,
                        'ecriture_categorie_id' => $avsParam->ecriture_categorie_id,
                        'date' => $decompte->date,
                        'heure' => "00:00:00",

                        'decompte_id' => $decompte->id,
                        'compte_id' => $avsParam->compte_id,
                        'sapeur_id' => null,

                        'module' => ImputationBusiness::ECRITURE_MODULE_AVS,
                        'type' => ImputationBusiness::ECRITURE_CATEGORIE_IMPOSITION_CHARGE_AVS_AC,
                    ];
                    if ($ecritureAvsGlobale['tarif'] > 0) {
                        Ecriture::on($db)->insert($ecritureAvsGlobale);
                    }
                }
            }
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
