<?php

namespace App\Application\Console\Commands;

use App\Infrastructure\Models\TypeUnite;
use Illuminate\Console\Command;

class CustomSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed-custom:dbs';

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
            printf("DATABASE " . $db . "\n");
            // printf("Seed unites\n");
            // TypeUnite::on($db)->insert(['id' => '7', 'comptable' => True, 'unite' => 'mois', 'abreviation' => 'm']);
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
