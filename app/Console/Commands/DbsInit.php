<?php

namespace App\Console\Commands;

use App\Support\Sis;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DbsInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbs:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialises les bases de données';

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
        $dbs = Sis::keys();
        foreach ($dbs as $db) {
            printf("DATABASE " . $db . "\n");
            printf("migrate:fresh with seed\n");
            Artisan::call('migrate:fresh --seed --database=' . Sis::connection($db));
            printf("migration done for " . $db . "\n");
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
