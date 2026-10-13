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
use App\Models\TuyauDiametre;
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

            // TODO: When needed

            printf("\n");
        });
        printf("Migrating done\n");
        return 0;
    }
}
