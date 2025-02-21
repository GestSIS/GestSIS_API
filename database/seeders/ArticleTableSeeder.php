<?php

namespace Database\Seeders;

use App\Infrastructure\Models\MaterielGenerique;
use App\Infrastructure\Models\MaterielNominal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // TODO: Générer du matériel en stock
        DB::table('articles')->insert([]);
    }
}
