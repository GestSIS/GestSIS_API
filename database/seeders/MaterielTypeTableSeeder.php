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
            ['id' => 1, 'tri' => 1, 'materiel_categorie_id' => 8, 'type' => 0, 'designation' => 'Pantalon attente F1 + ceinture', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => true],
            ['id' => 2, 'tri' => 2, 'materiel_categorie_id' => 8, 'type' => 0, 'designation' => 'Pantalon feu', 'est_numerote' => true, 'est_attribuable' => true, 'est_taillee' => true],
            ['id' => 3, 'tri' => 3, 'materiel_categorie_id' => 9, 'type' => 0, 'designation' => 'Veste attente F1', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => true],
            ['id' => 4, 'tri' => 4, 'materiel_categorie_id' => 9, 'type' => 0, 'designation' => 'Veste feu', 'est_numerote' => true, 'est_attribuable' => true, 'est_taillee' => true],
            ['id' => 5, 'tri' => 5, 'materiel_categorie_id' => 10, 'type' => 0, 'designation' => 'T-Shirt', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => true],
            ['id' => 6, 'tri' => 6, 'materiel_categorie_id' => 10, 'type' => 0, 'designation' => 'Sweatshirt', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => true],
            ['id' => 7, 'tri' => 7, 'materiel_categorie_id' => 11, 'type' => 0, 'designation' => 'Cagoule', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => false],
            ['id' => 8, 'tri' => 8, 'materiel_categorie_id' => 11, 'type' => 0, 'designation' => 'Pull', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => true],
            ['id' => 9, 'tri' => 9, 'materiel_categorie_id' => 11, 'type' => 0, 'designation' => 'Collants', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => true],
            ['id' => 10, 'tri' => 10, 'materiel_categorie_id' => 3, 'type' => 0, 'designation' => 'Casque F1', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => true],
            ['id' => 11, 'tri' => 11, 'materiel_categorie_id' => 4, 'type' => 0, 'designation' => 'Bottes Haix', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => true],
            ['id' => 12, 'tri' => 12, 'materiel_categorie_id' => 5, 'type' => 0, 'designation' => 'Anneau cousu + mousqueton', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => false],
            ['id' => 13, 'tri' => 13, 'materiel_categorie_id' => 5, 'type' => 0, 'designation' => 'Cale caoutchouc', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => false],
            ['id' => 14, 'tri' => 14, 'materiel_categorie_id' => 5, 'type' => 0, 'designation' => 'Couteau', 'est_numerote' => true, 'est_attribuable' => true, 'est_taillee' => false],
            ['id' => 15, 'tri' => 15, 'materiel_categorie_id' => 6, 'type' => 0, 'designation' => 'Kaba', 'est_numerote' => true, 'est_attribuable' => true, 'est_taillee' => false],
            ['id' => 16, 'tri' => 16, 'materiel_categorie_id' => 7, 'type' => 0, 'designation' => 'Gants de travail', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => true],
            ['id' => 17, 'tri' => 17, 'materiel_categorie_id' => 7, 'type' => 0, 'designation' => 'Gants feu', 'est_numerote' => false, 'est_attribuable' => true, 'est_taillee' => true],
            ['id' => 18, 'tri' => 18, 'materiel_categorie_id' => 5, 'type' => 3, 'designation' => 'Tonne-Pompe', 'est_numerote' => false, 'est_attribuable' => false, 'est_taillee' => false],
            ['id' => 19, 'tri' => 19, 'materiel_categorie_id' => 5, 'type' => 3, 'designation' => 'Véhicule module', 'est_numerote' => false, 'est_attribuable' => false, 'est_taillee' => false],
            ['id' => 20, 'tri' => 20, 'materiel_categorie_id' => 5, 'type' => 3, 'designation' => 'Véhicule transport', 'est_numerote' => false, 'est_attribuable' => false, 'est_taillee' => false],
            ['id' => 21, 'tri' => 21, 'materiel_categorie_id' => 5, 'type' => 3, 'designation' => 'Véhicule première intervention', 'est_numerote' => false, 'est_attribuable' => false, 'est_taillee' => false],
            ['id' => 22, 'tri' => 22, 'materiel_categorie_id' => 5, 'type' => 3, 'designation' => 'Véhicule pionnier', 'est_numerote' => false, 'est_attribuable' => false, 'est_taillee' => false],
        ]);
    }
}
