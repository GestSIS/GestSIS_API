<?php

namespace App\Application\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DbsMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbs:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate les bases de données';

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
            printf("DATABASE " . $db . "\n");
            printf("migrate\n");
            Artisan::call('migrate --database=' . $db);
            // Artisan::call('migrate:rollback --step=1 --database=' . $db);
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
