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
        // Catégories EPI (matériel attribuable aux sapeurs)
        DB::table('materiel_categories')->insert([
            ['id' => 1, 'tri' => 1, 'couleur_id' => 3, 'designation' => 'EPI', 'parent_id' => null],
            ['id' => 2, 'tri' => 2, 'couleur_id' => 3, 'designation' => 'Habits', 'parent_id' => 1],
            ['id' => 3, 'tri' => 3, 'couleur_id' => 3, 'designation' => 'Casques', 'parent_id' => 1],
            ['id' => 4, 'tri' => 4, 'couleur_id' => 3, 'designation' => 'Souliers', 'parent_id' => 1],
            ['id' => 5, 'tri' => 5, 'couleur_id' => 3, 'designation' => 'Matériel divers', 'parent_id' => 1],
            ['id' => 6, 'tri' => 6, 'couleur_id' => 3, 'designation' => 'Clés / Badges', 'parent_id' => 1],
            ['id' => 7, 'tri' => 7, 'couleur_id' => 3, 'designation' => 'Gants', 'parent_id' => 1],
            ['id' => 8, 'tri' => 8, 'couleur_id' => 3, 'designation' => 'Pantalon', 'parent_id' => 2],
            ['id' => 9, 'tri' => 9, 'couleur_id' => 3, 'designation' => 'Veste', 'parent_id' => 2],
            ['id' => 10, 'tri' => 10, 'couleur_id' => 3, 'designation' => 'Pull', 'parent_id' => 2],
            ['id' => 11, 'tri' => 11, 'couleur_id' => 3, 'designation' => 'Sous-vêtements', 'parent_id' => 2],
        ]);

        // Catégories du matériel d'intervention, reprises du catalogue de gestion du matériel SIS BA
        DB::table('materiel_categories')->insert([
            ['id' => 12, 'tri' => 12, 'couleur_id' => 11, 'designation' => 'Tuyaux et dévidoirs', 'parent_id' => null],
            ['id' => 13, 'tri' => 13, 'couleur_id' => 11, 'designation' => 'Lances / Divisions / Réductions / Clés BH', 'parent_id' => null],
            ['id' => 14, 'tri' => 14, 'couleur_id' => 11, 'designation' => 'Accessoires Tuyaux', 'parent_id' => null],
            ['id' => 15, 'tri' => 15, 'couleur_id' => 11, 'designation' => 'Mousse et Extincteurs', 'parent_id' => null],
            ['id' => 16, 'tri' => 16, 'couleur_id' => 11, 'designation' => 'Extinction Divers', 'parent_id' => null],
            ['id' => 17, 'tri' => 17, 'couleur_id' => 12, 'designation' => 'Echelles / Sauvetage', 'parent_id' => null],
            ['id' => 18, 'tri' => 18, 'couleur_id' => 12, 'designation' => 'Lampes / Electricité', 'parent_id' => null],
            ['id' => 19, 'tri' => 19, 'couleur_id' => 12, 'designation' => 'Sanitaire / Circulation', 'parent_id' => null],
            ['id' => 20, 'tri' => 20, 'couleur_id' => 12, 'designation' => 'Sécurité / Equipement', 'parent_id' => null],
            ['id' => 21, 'tri' => 21, 'couleur_id' => 12, 'designation' => 'Commandement / Intendance', 'parent_id' => null],
            ['id' => 22, 'tri' => 22, 'couleur_id' => 12, 'designation' => 'Outils de pionnier', 'parent_id' => null],
            ['id' => 23, 'tri' => 23, 'couleur_id' => 12, 'designation' => 'Outillage et caisses', 'parent_id' => null],
            ['id' => 24, 'tri' => 24, 'couleur_id' => 14, 'designation' => 'Protection Respiratoire', 'parent_id' => null],
            ['id' => 25, 'tri' => 25, 'couleur_id' => 13, 'designation' => 'Anti-chutes', 'parent_id' => null],
            ['id' => 26, 'tri' => 26, 'couleur_id' => 10, 'designation' => 'Véhicules / Remorques', 'parent_id' => null],
            ['id' => 27, 'tri' => 27, 'couleur_id' => 10, 'designation' => 'Motopompes', 'parent_id' => null],
            ['id' => 28, 'tri' => 28, 'couleur_id' => 10, 'designation' => 'Moteurs / Carburants / Produits', 'parent_id' => null],
            ['id' => 29, 'tri' => 29, 'couleur_id' => 10, 'designation' => 'Accessoires Véhicules', 'parent_id' => null],
            ['id' => 30, 'tri' => 30, 'couleur_id' => 15, 'designation' => 'Inondations', 'parent_id' => null],
            ['id' => 31, 'tri' => 31, 'couleur_id' => 15, 'designation' => 'Hydrocarbures', 'parent_id' => null],
        ]);
    }
}
