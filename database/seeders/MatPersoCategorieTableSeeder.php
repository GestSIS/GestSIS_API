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
    public function run()
    {
        $categories = array(
            array('id' => 1, 'designation' => 'Habits', 'pere_id' => null),
            array('id' => 2, 'designation' => 'Casques', 'pere_id' => null),
            array('id' => 3, 'designation' => 'Souliers', 'pere_id' => null),
            array('id' => 4, 'designation' => 'Matériel divers', 'pere_id' => null),
            array('id' => 5, 'designation' => 'Clés/Badges', 'pere_id' => null),
            array('id' => 6, 'designation' => 'Gants', 'pere_id' => null),
            array('id' => 7, 'designation' => 'Pantalon', 'pere_id' => 1),
            array('id' => 8, 'designation' => 'Veste', 'pere_id' => 1),
            array('id' => 9, 'designation' => 'Pull', 'pere_id' => 1),
            array('id' => 10, 'designation' => 'Sous-vêtements', 'pere_id' => 1),
        );
        DB::table('materiel_categories')->insert($categories);

        $types = array(
            array('id' => 1, 'designation' => 'Pantalon attente F1 + ceinture', 'materiel_categorie_id' => 7),
            array('id' => 2, 'designation' => 'Pantalon feu', 'materiel_categorie_id' => 7),
            array('id' => 3, 'designation' => 'Veste attente F1', 'materiel_categorie_id' => 8),
            array('id' => 4, 'designation' => 'Veste feu', 'materiel_categorie_id' => 8),
            array('id' => 5, 'designation' => 'T-Shirt', 'materiel_categorie_id' => 9),
            array('id' => 6, 'designation' => 'Sweatshirt', 'materiel_categorie_id' => 9),
            array('id' => 7, 'designation' => 'Cagoule', 'materiel_categorie_id' => 10),
            array('id' => 8, 'designation' => 'Pull', 'materiel_categorie_id' => 10),
            array('id' => 9, 'designation' => 'Collants', 'materiel_categorie_id' => 10),
            array('id' => 10, 'designation' => 'Casque F1', 'materiel_categorie_id' => 2),
            array('id' => 11, 'designation' => 'Bottes Haix', 'materiel_categorie_id' => 3),
            array('id' => 12, 'designation' => 'Anneau cousu + mousqueton', 'materiel_categorie_id' => 4),
            array('id' => 13, 'designation' => 'Cale caoutchouc', 'materiel_categorie_id' => 4),
            array('id' => 14, 'designation' => 'Couteau', 'materiel_categorie_id' => 4),
            array('id' => 15, 'designation' => 'Kaba', 'materiel_categorie_id' => 5),
            array('id' => 16, 'designation' => 'Gants de travail', 'materiel_categorie_id' => 6),
            array('id' => 17, 'designation' => 'Gants feu', 'materiel_categorie_id' => 6),
        );
        DB::table('materiel_types')->insert($types);
    }
}
