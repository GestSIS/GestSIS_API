<?php

namespace App\Console\Commands;

use App\Infrastructure\Models\Cours;
use App\Infrastructure\Models\Grade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DbsSeed extends Command
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
    protected $description = 'Seed les bases de données';

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
            // Artisan::call('db:seed --database=db_' . $db);
            $grade = Grade::on('db_' . $db)->where('abreviation', '=', 'adj')->first();
            if ($grade === null) {
                Grade::on('db_' . $db)->insert(['id' => 12, 'designation' => 'Adjudant', 'abreviation' => 'Adj', 'groupe' => 2, 'tri' => 67]);
            }

            Cours::on('db_' . $db)->where('designation', '=', 'Chef d\'intervention 1')->update(['grade_id' => $grade?->id ?? 12]);
            Cours::on('db_' . $db)->where('designation', '=', 'Chef d\'intervention 2')->update(['grade_id' => 3]);
            Cours::on('db_' . $db)->where('designation', '=', 'Machiniste')->update(['grade_id' => 7]);
            Cours::on('db_' . $db)->where('designation', '=', 'Chef de groupe')->update(['grade_id' => 6]);
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
