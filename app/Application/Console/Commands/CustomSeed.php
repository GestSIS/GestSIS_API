<?php

namespace App\Application\Console\Commands;

use App\Infrastructure\Models\Localite;
use App\Infrastructure\Models\TypeUnite;
use Illuminate\Console\Command;

class CustomSeed extends Command
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
            printf("DATABASE " . $db . "\n");
            Localite::on($db)->insert(['id' => '149', 'commune_id' => NULL, 'npa' => '2523', 'designation' => 'Lignières NE']);
            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
