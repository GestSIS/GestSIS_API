<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterielCategorieTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('materiel_categories')->insert([
            ['id' => 1, 'tri' => 1, 'couleur_id' => 3, 'designation' => 'Habits', 'parent_id' => null],
            ['id' => 2, 'tri' => 2, 'couleur_id' => 3, 'designation' => 'Casques', 'parent_id' => null],
            ['id' => 3, 'tri' => 3, 'couleur_id' => 3, 'designation' => 'Souliers', 'parent_id' => null],
            ['id' => 4, 'tri' => 4, 'couleur_id' => 3, 'designation' => 'Matériel divers', 'parent_id' => null],
            ['id' => 5, 'tri' => 5, 'couleur_id' => 3, 'designation' => 'Clés/Badges', 'parent_id' => null],
            ['id' => 6, 'tri' => 6, 'couleur_id' => 3, 'designation' => 'Gants', 'parent_id' => null],
            ['id' => 7, 'tri' => 7, 'couleur_id' => 3, 'designation' => 'Pantalon', 'parent_id' => 1],
            ['id' => 8, 'tri' => 8, 'couleur_id' => 3, 'designation' => 'Veste', 'parent_id' => 1],
            ['id' => 9, 'tri' => 9, 'couleur_id' => 3, 'designation' => 'Pull', 'parent_id' => 1],
            ['id' => 10, 'tri' => 10, 'couleur_id' => 3, 'designation' => 'Sous-vêtements', 'parent_id' => 1],
        ]);
    }
}
