<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterielTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('materiel_types')->insert([
            ['id' => 1, 'tri' => 1, 'designation' => 'Pantalon attente F1 + ceinture', 'materiel_categorie_id' => 7, 'prix' => '10'],
            ['id' => 2, 'tri' => 2, 'designation' => 'Pantalon feu', 'materiel_categorie_id' => 7, 'prix' => '10'],
            ['id' => 3, 'tri' => 3, 'designation' => 'Veste attente F1', 'materiel_categorie_id' => 8, 'prix' => '10'],
            ['id' => 4, 'tri' => 4, 'designation' => 'Veste feu', 'materiel_categorie_id' => 8, 'prix' => '10'],
            ['id' => 5, 'tri' => 5, 'designation' => 'T-Shirt', 'materiel_categorie_id' => 9, 'prix' => '10'],
            ['id' => 6, 'tri' => 6, 'designation' => 'Sweatshirt', 'materiel_categorie_id' => 9, 'prix' => '10'],
            ['id' => 7, 'tri' => 7, 'designation' => 'Cagoule', 'materiel_categorie_id' => 10, 'prix' => '10'],
            ['id' => 8, 'tri' => 8, 'designation' => 'Pull', 'materiel_categorie_id' => 10, 'prix' => '10'],
            ['id' => 9, 'tri' => 9, 'designation' => 'Collants', 'materiel_categorie_id' => 10, 'prix' => '10'],
            ['id' => 10, 'tri' => 10, 'designation' => 'Casque F1', 'materiel_categorie_id' => 2, 'prix' => '10'],
            ['id' => 11, 'tri' => 11, 'designation' => 'Bottes Haix', 'materiel_categorie_id' => 3, 'prix' => '10'],
            ['id' => 12, 'tri' => 12, 'designation' => 'Anneau cousu + mousqueton', 'materiel_categorie_id' => 4, 'prix' => '10'],
            ['id' => 13, 'tri' => 13, 'designation' => 'Cale caoutchouc', 'materiel_categorie_id' => 4, 'prix' => '10'],
            ['id' => 14, 'tri' => 14, 'designation' => 'Couteau', 'materiel_categorie_id' => 4, 'prix' => '10'],
            ['id' => 15, 'tri' => 15, 'designation' => 'Kaba', 'materiel_categorie_id' => 5, 'prix' => '10'],
            ['id' => 16, 'tri' => 16, 'designation' => 'Gants de travail', 'materiel_categorie_id' => 6, 'prix' => '10'],
            ['id' => 17, 'tri' => 17, 'designation' => 'Gants feu', 'materiel_categorie_id' => 6, 'prix' => '10'],
        ]);
    }
}
