<?php

namespace App\Console\Commands;

use App\Domaine\Business\ExerciceBusiness;
use App\Models\Exercice;
use App\Support\Sis;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DbsExercicesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbs:exercices-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcule le status des exercices';

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
            printf("Recompute for sis=" . $db . "\n");
            $exercices = Exercice::where('date', '>', Carbon::create(2023, 1, 1));
            foreach ($exercices as $exercice) {
                ExerciceBusiness::updateStatut($exercice->id);
            }

            printf("\n");
        });
        printf("Migrating done\n");
        return 0;
    }
}
