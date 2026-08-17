<?php

namespace App\Console\Commands;

use App\Models\Cours;
use App\Models\Grade;
use App\Support\Sis;
use Exception;
use Illuminate\Console\Command;

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
            // $grade = Grade::on('db_' . $db)->where('abreviation', '=', 'Adj')->first() ?? Grade::on('db_' . $db)->where('abreviation', '=', 'adj')->first();
            // if ($grade === null) {
            //     try {
            //         Grade::on('db_' . $db)->insert(['id' => 12, 'designation' => 'Adjudant', 'abreviation' => 'Adj', 'groupe' => 2, 'tri' => 67]);
            //     } catch (Exception $e) {
            //         printf("Unable to create grade for db : " . $db);
            //     }
            // }

            // Cours::on('db_' . $db)->where('designation', '=', 'Chef d\'intervention 1')->update(['grade_id' => $grade?->id ?? 12]);
            // Cours::on('db_' . $db)->where('designation', '=', 'Chef d\'intervention 2')->update(['grade_id' => 3]);
            // Cours::on('db_' . $db)->where('designation', '=', 'Machiniste')->update(['grade_id' => 7]);
            // Cours::on('db_' . $db)->where('designation', '=', 'Chef de groupe')->update(['grade_id' => 6]);
            // TODO: if jsp dans $db
            if (str_contains($db, 'jsp')) {
                printf("Seeding db=db_" . $db . "\n");
                Cours::on(Sis::connection($db))->insert([
                    ['designation' => 'JSP Module 1', 'abreviation' => 'JSP 1', 'tri' => 1],
                    ['designation' => 'JSP Module 2', 'abreviation' => 'JSP 2', 'tri' => 2],
                    ['designation' => 'JSP Module 3', 'abreviation' => 'JSP 3', 'tri' => 3],
                    ['designation' => 'JSP Module 4', 'abreviation' => 'JSP 4', 'tri' => 4],
                    ['designation' => 'JSP Module 5', 'abreviation' => 'JSP 5', 'tri' => 5],
                ]);
            }
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
