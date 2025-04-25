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
        DB::table('articles')->insert([
            ['materiel_type_id' => 1, 'emplacement_id' => 13, 'uuid' => '2387667wd661', 'numero' => '', 'taille' => 'S', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 2, 'emplacement_id' => 13, 'uuid' => '2387667wd662', 'numero' => 'Xsdfgbsdf23', 'taille' => 'S', 'remarque' => '', 'est_etiquete' => true, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 3, 'emplacement_id' => 13, 'uuid' => '2387667wd663', 'numero' => '', 'taille' => 'S', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 4, 'emplacement_id' => 13, 'uuid' => '2387667wd664', 'numero' => 'Xsdtzf72zsdf', 'taille' => 'S', 'remarque' => '', 'est_etiquete' => true, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 5, 'emplacement_id' => 13, 'uuid' => '2387667wd665', 'numero' => '', 'taille' => 'S', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 6, 'emplacement_id' => 13, 'uuid' => '2387667wd666', 'numero' => '', 'taille' => 'S', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 7, 'emplacement_id' => 13, 'uuid' => '2387667wd667', 'numero' => '', 'taille' => 'S', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 8, 'emplacement_id' => 13, 'uuid' => '2387667wd668', 'numero' => '', 'taille' => 'S', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 9, 'emplacement_id' => 13, 'uuid' => '2387667wd669', 'numero' => '', 'taille' => 'S', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 10, 'emplacement_id' => 13, 'uuid' => '2387667wd6610', 'numero' => '', 'taille' => '', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 11, 'emplacement_id' => 13, 'uuid' => '2387667wd6611', 'numero' => '', 'taille' => '52', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 12, 'emplacement_id' => 13, 'uuid' => '2387667wd6612', 'numero' => '', 'taille' => '', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 13, 'emplacement_id' => 13, 'uuid' => '2387667wd6613', 'numero' => '', 'taille' => '', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 14, 'emplacement_id' => 13, 'uuid' => '2387667wd6614', 'numero' => '', 'taille' => '', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 15, 'emplacement_id' => 13, 'uuid' => '2387667wd6615', 'numero' => '', 'taille' => '', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 16, 'emplacement_id' => 13, 'uuid' => '2387667wd6616', 'numero' => '', 'taille' => 'S', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
            ['materiel_type_id' => 17, 'emplacement_id' => 13, 'uuid' => '2387667wd6617', 'numero' => '', 'taille' => 'S', 'remarque' => '', 'est_etiquete' => false, 'achat' => '2011', 'created_at' => '2025-01-01'],
        ]);

    }
}
