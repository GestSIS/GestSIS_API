<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MatPersoCategorieTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'designation' => 'Habits', 'parent_id' => null],
            ['id' => 2, 'designation' => 'Casques', 'parent_id' => null],
            ['id' => 3, 'designation' => 'Souliers', 'parent_id' => null],
            ['id' => 4, 'designation' => 'Matériel divers', 'parent_id' => null],
            ['id' => 5, 'designation' => 'Clés/Badges', 'parent_id' => null],
            ['id' => 6, 'designation' => 'Gants', 'parent_id' => null],
            ['id' => 7, 'designation' => 'Pantalon', 'parent_id' => 1],
            ['id' => 8, 'designation' => 'Veste', 'parent_id' => 1],
            ['id' => 9, 'designation' => 'Pull', 'parent_id' => 1],
            ['id' => 10, 'designation' => 'Sous-vêtements', 'parent_id' => 1],
        ];
        DB::table('materiel_categories')->insert($categories);

        DB::table('materiel_types')->insert([
            ['id' => 1, 'designation' => 'Pantalon attente F1 + ceinture', 'materiel_categorie_id' => 7],
            ['id' => 2, 'designation' => 'Pantalon feu', 'materiel_categorie_id' => 7],
            ['id' => 3, 'designation' => 'Veste attente F1', 'materiel_categorie_id' => 8],
            ['id' => 4, 'designation' => 'Veste feu', 'materiel_categorie_id' => 8],
            ['id' => 5, 'designation' => 'T-Shirt', 'materiel_categorie_id' => 9],
            ['id' => 6, 'designation' => 'Sweatshirt', 'materiel_categorie_id' => 9],
            ['id' => 7, 'designation' => 'Cagoule', 'materiel_categorie_id' => 10],
            ['id' => 8, 'designation' => 'Pull', 'materiel_categorie_id' => 10],
            ['id' => 9, 'designation' => 'Collants', 'materiel_categorie_id' => 10],
            ['id' => 10, 'designation' => 'Casque F1', 'materiel_categorie_id' => 2],
            ['id' => 11, 'designation' => 'Bottes Haix', 'materiel_categorie_id' => 3],
            ['id' => 12, 'designation' => 'Anneau cousu + mousqueton', 'materiel_categorie_id' => 4],
            ['id' => 13, 'designation' => 'Cale caoutchouc', 'materiel_categorie_id' => 4],
            ['id' => 14, 'designation' => 'Couteau', 'materiel_categorie_id' => 4],
            ['id' => 15, 'designation' => 'Kaba', 'materiel_categorie_id' => 5],
            ['id' => 16, 'designation' => 'Gants de travail', 'materiel_categorie_id' => 6],
            ['id' => 17, 'designation' => 'Gants feu', 'materiel_categorie_id' => 6],
        ]);
    }
}
