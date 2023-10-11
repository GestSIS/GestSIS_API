<?php

namespace App\Application\Console\Commands;

use App\Domaine\Business\ExerciceBusiness;
use App\Domaine\Business\SapeurBusiness;
use App\Domaine\SPI\ExerciceRepository;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\ExerciceComptable;
use App\Infrastructure\Repositories\ExerciceRepositoryEloquent;
use App\Infrastructure\Repositories\SapeurRepositoryEloquent;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

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
        $dbs = config('database.dbs');
        $exerciceBusiness = new ExerciceBusiness(new ExerciceRepositoryEloquent());
        foreach ($dbs as $db) {
            printf("Recompute for sis=" . $db . "\n");
            Config::set('database.default', $db);
            $exercices = Exercice::where('date', '>', Carbon::create(2023, 1, 1));
            foreach ($exercices as $exercice) {
                $exerciceBusiness->updateStatut($exercice->id);
            }

            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
