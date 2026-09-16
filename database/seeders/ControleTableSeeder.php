<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ControleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Suppose ArticleTableSeeder, VehiculeTableSeeder et SapeursTableSeeder déjà joués :
     * les articles EPI ont pour id (8 + materiel_type_id) pour materiel_type_id 1 à 17,
     * les véhicules (materiel_type_id 18-22) ont les id d'article 1 à 8, et le sapeur 2
     * existe (utilisé par ailleurs comme sapeur de référence pour les seeders de démo).
     */
    public function run(): void
    {
        $sapeurId = 2;

        DB::table('controles')->insert([
            ['id' => 1, 'nom' => 'Contrôle visuel EPI', 'description' => "Vérification de l'état général et de la propreté de l'habit de protection.", 'recurrence_type' => 'PERIODIQUE', 'recurrence_value' => 12, 'duree_preavis' => 1, 'externe' => false, 'reparateur' => 'Chef mat', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nom' => 'Contrôle casque F1', 'description' => 'Contrôle de la résistance de la coque.', 'recurrence_type' => 'PERIODIQUE', 'recurrence_value' => 24, 'duree_preavis' => 2, 'externe' => false, 'reparateur' => 'Chef mat', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nom' => 'Contrôle technique véhicule', 'description' => 'Contrôle technique effectué par le garage partenaire.', 'recurrence_type' => 'PERIODIQUE', 'recurrence_value' => 6, 'duree_preavis' => 1, 'externe' => true, 'reparateur' => 'Garage Girardin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nom' => 'Vérification après intervention', 'description' => 'À réaliser après chaque intervention ayant sollicité le matériel.', 'recurrence_type' => 'NON_PERIODIQUE', 'recurrence_value' => null, 'duree_preavis' => null, 'externe' => false, 'reparateur' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nom' => 'Entretien bottes', 'description' => "Contrôle de l'état des semelles, selon usure constatée.", 'recurrence_type' => 'NON_PERIODIQUE', 'recurrence_value' => null, 'duree_preavis' => null, 'externe' => false, 'reparateur' => 'Chef mat', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('controle_taches')->insert([
            ['id' => 1, 'controle_id' => 1, 'order' => 1, 'nom' => 'État général', 'description' => null, 'type' => 'BOOLEAN', 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'controle_id' => 1, 'order' => 2, 'nom' => 'Propreté', 'description' => null, 'type' => 'BOOLEAN', 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'controle_id' => 2, 'order' => 1, 'nom' => 'Résistance coque', 'description' => 'Test de résistance au choc', 'type' => 'NUMERIC', 'unit' => 'N', 'value_min' => 100, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'controle_id' => 3, 'order' => 1, 'nom' => 'Niveau des fluides', 'description' => null, 'type' => 'BOOLEAN', 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'controle_id' => 3, 'order' => 2, 'nom' => 'Pression pneus', 'description' => null, 'type' => 'NUMERIC', 'unit' => 'bar', 'value_min' => 2, 'value_max' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'controle_id' => 4, 'order' => 1, 'nom' => 'Pas de déformation visible', 'description' => null, 'type' => 'BOOLEAN', 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'controle_id' => 5, 'order' => 1, 'nom' => 'Semelle intacte', 'description' => null, 'type' => 'BOOLEAN', 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('controle_materiel_types')->insert([
            ['controle_id' => 1, 'materiel_type_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 1, 'materiel_type_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 2, 'materiel_type_id' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 3, 'materiel_type_id' => 18, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 3, 'materiel_type_id' => 19, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 3, 'materiel_type_id' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 3, 'materiel_type_id' => 21, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 4, 'materiel_type_id' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 5, 'materiel_type_id' => 11, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Historique d'exécutions, avec des dates choisies pour peupler le tableau de
        // bord de façon réaliste : en retard, en préavis, à jour et jamais contrôlé
        // (le casque F1, contrôle 2, n'a volontairement aucune exécution).
        DB::table('controle_execs')->insert([
            ['id' => 1, 'controle_id' => 1, 'article_id' => 9, 'executed_at' => now()->subMonths(13), 'executed_by' => $sapeurId, 'trigger_type' => 'PERIODIQUE', 'remarque_globale' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'controle_id' => 1, 'article_id' => 11, 'executed_at' => now()->subMonths(11)->subDays(20), 'executed_by' => $sapeurId, 'trigger_type' => 'PERIODIQUE', 'remarque_globale' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'controle_id' => 3, 'article_id' => 1, 'executed_at' => now()->subMonths(7), 'executed_by' => $sapeurId, 'trigger_type' => 'PERIODIQUE', 'remarque_globale' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'controle_id' => 3, 'article_id' => 2, 'executed_at' => now()->subMonths(5)->subDays(20), 'executed_by' => $sapeurId, 'trigger_type' => 'PERIODIQUE', 'remarque_globale' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'controle_id' => 3, 'article_id' => 3, 'executed_at' => now()->subMonths(2), 'executed_by' => $sapeurId, 'trigger_type' => 'PERIODIQUE', 'remarque_globale' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'controle_id' => 4, 'article_id' => 20, 'executed_at' => now()->subMonths(3), 'executed_by' => $sapeurId, 'trigger_type' => 'NON_PERIODIQUE', 'remarque_globale' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'controle_id' => 5, 'article_id' => 19, 'executed_at' => now()->subMonths(4), 'executed_by' => $sapeurId, 'trigger_type' => 'NON_PERIODIQUE', 'remarque_globale' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('controle_exec_taches')->insert([
            // Contrôle visuel EPI, article 9 (Pantalon attente F1) : anomalie de propreté
            ['controle_exec_id' => 1, 'tache_id' => 1, 'task_order_snapshot' => 1, 'statut' => 'OK', 'value_measured' => null, 'value_min_snapshot' => null, 'value_max_snapshot' => null, 'value_in_range' => null, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            ['controle_exec_id' => 1, 'tache_id' => 2, 'task_order_snapshot' => 2, 'statut' => 'KO', 'value_measured' => null, 'value_min_snapshot' => null, 'value_max_snapshot' => null, 'value_in_range' => null, 'remarque' => 'Taches visibles, à nettoyer', 'created_at' => now(), 'updated_at' => now()],
            // Contrôle visuel EPI, article 11 (Veste attente F1) : conforme
            ['controle_exec_id' => 2, 'tache_id' => 1, 'task_order_snapshot' => 1, 'statut' => 'OK', 'value_measured' => null, 'value_min_snapshot' => null, 'value_max_snapshot' => null, 'value_in_range' => null, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            ['controle_exec_id' => 2, 'tache_id' => 2, 'task_order_snapshot' => 2, 'statut' => 'OK', 'value_measured' => null, 'value_min_snapshot' => null, 'value_max_snapshot' => null, 'value_in_range' => null, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            // Contrôle technique véhicule, article 1 (Tonne-Pompe) : conforme
            ['controle_exec_id' => 3, 'tache_id' => 4, 'task_order_snapshot' => 1, 'statut' => 'OK', 'value_measured' => null, 'value_min_snapshot' => null, 'value_max_snapshot' => null, 'value_in_range' => null, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            ['controle_exec_id' => 3, 'tache_id' => 5, 'task_order_snapshot' => 2, 'statut' => null, 'value_measured' => 2.5, 'value_min_snapshot' => 2, 'value_max_snapshot' => 3, 'value_in_range' => true, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            // Contrôle technique véhicule, article 2 (VPM) : pression insuffisante
            ['controle_exec_id' => 4, 'tache_id' => 4, 'task_order_snapshot' => 1, 'statut' => 'OK', 'value_measured' => null, 'value_min_snapshot' => null, 'value_max_snapshot' => null, 'value_in_range' => null, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            ['controle_exec_id' => 4, 'tache_id' => 5, 'task_order_snapshot' => 2, 'statut' => null, 'value_measured' => 1.8, 'value_min_snapshot' => 2, 'value_max_snapshot' => 3, 'value_in_range' => false, 'remarque' => 'Pression insuffisante, à regonfler', 'created_at' => now(), 'updated_at' => now()],
            // Contrôle technique véhicule, article 3 (Iveco) : conforme
            ['controle_exec_id' => 5, 'tache_id' => 4, 'task_order_snapshot' => 1, 'statut' => 'OK', 'value_measured' => null, 'value_min_snapshot' => null, 'value_max_snapshot' => null, 'value_in_range' => null, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            ['controle_exec_id' => 5, 'tache_id' => 5, 'task_order_snapshot' => 2, 'statut' => null, 'value_measured' => 2.4, 'value_min_snapshot' => 2, 'value_max_snapshot' => 3, 'value_in_range' => true, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            // Vérification après intervention, article 20 (Anneau cousu + mousqueton)
            ['controle_exec_id' => 6, 'tache_id' => 6, 'task_order_snapshot' => 1, 'statut' => 'OK', 'value_measured' => null, 'value_min_snapshot' => null, 'value_max_snapshot' => null, 'value_in_range' => null, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            // Entretien bottes, article 19 (Bottes Haix)
            ['controle_exec_id' => 7, 'tache_id' => 7, 'task_order_snapshot' => 1, 'statut' => 'OK', 'value_measured' => null, 'value_min_snapshot' => null, 'value_max_snapshot' => null, 'value_in_range' => null, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
