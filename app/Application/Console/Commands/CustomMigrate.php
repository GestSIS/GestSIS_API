<?php

namespace App\Application\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CustomMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:dbs';

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
        $dbs = explode(",", env('DB_LISTE', true));
        foreach ($dbs as $db) {
            printf("DATABASE ".$db."\n");
            printf("migrate:reset\n");
            Artisan::call('migrate:reset --database='.$db);
            printf("migrate\n");
            Artisan::call('migrate --database='.$db);
            printf("db:seed\n");
            Artisan::call('db:seed --database='.$db);
            printf("\n");
        }
        printf("Migrating done");
        return 0;
    }
}
