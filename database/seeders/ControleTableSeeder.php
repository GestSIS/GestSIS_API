<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ControleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Suppose MaterielTypeTableSeeder, ArticleTableSeeder, VehiculeTableSeeder et
     * SapeursTableSeeder déjà joués : les véhicules (materiel_type_id 18-22) ont les id
     * d'article 1 à 8, les types Dräger sont les materiel_type_id 23 à 25, et le sapeur 2
     * existe (utilisé par ailleurs comme sapeur de référence pour les seeders de démo).
     *
     * Les contrôles 4 à 6 reprennent les feuilles de contrôle Dräger Schweiz (édition
     * 2020). Ils portent sur le contrôle annuel, qui comprend toutes les colonnes ; la
     * date, la remarque et le visa de ces feuilles sont les champs de l'exécution.
     */
    public function run(): void
    {
        $sapeurId = 2;

        DB::table('controles')->insert([
            ['id' => 1, 'nom' => 'Contrôle technique véhicule', 'description' => 'Contrôle technique effectué par le garage partenaire.', 'recurrence_type' => 'PERIODIQUE', 'recurrence_value' => 6, 'duree_preavis' => 1, 'nb_execution_max' => null, 'nb_execution_preavis' => null, 'externe' => true, 'reparateur' => 'Garage Girardin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nom' => 'Lavage', 'description' => 'Lavage des tenues feu, à saisir à chaque passage.', 'recurrence_type' => 'NON_PERIODIQUE', 'recurrence_value' => null, 'duree_preavis' => null, 'nb_execution_max' => 50, 'nb_execution_preavis' => 40, 'externe' => false, 'reparateur' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nom' => 'Contrôle RAG', 'description' => null, 'recurrence_type' => 'NON_PERIODIQUE', 'recurrence_value' => null, 'duree_preavis' => null, 'nb_execution_max' => null, 'nb_execution_preavis' => null, 'externe' => false, 'reparateur' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nom' => 'APR - Masque', 'description' => 'Feuille de contrôle Dräger pour masques avec surpression.', 'recurrence_type' => 'NON_PERIODIQUE', 'recurrence_value' => null, 'duree_preavis' => null, 'nb_execution_max' => null, 'nb_execution_preavis' => null, 'externe' => false, 'reparateur' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nom' => 'APR - Soupape à la demande', 'description' => 'Feuille de contrôle Dräger pour soupape à la demande avec surpression.', 'recurrence_type' => 'NON_PERIODIQUE', 'recurrence_value' => null, 'duree_preavis' => null, 'nb_execution_max' => null, 'nb_execution_preavis' => null, 'externe' => false, 'reparateur' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'nom' => 'APR - Appareil', 'description' => 'Feuille de contrôle Dräger pour appareils à air comprimé.', 'recurrence_type' => 'NON_PERIODIQUE', 'recurrence_value' => null, 'duree_preavis' => null, 'nb_execution_max' => null, 'nb_execution_preavis' => null, 'externe' => false, 'reparateur' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'nom' => 'Remplissage bouteille', 'description' => 'Remplissage bouteille', 'recurrence_type' => 'NON_PERIODIQUE', 'recurrence_value' => null, 'duree_preavis' => null, 'nb_execution_max' => null, 'nb_execution_preavis' => null, 'externe' => false, 'reparateur' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('controle_taches')->insert([
            // Contrôle technique véhicule
            ['id' => 1, 'controle_id' => 1, 'order' => 1, 'nom' => 'Niveau des fluides', 'description' => null, 'type' => 'BOOLEAN', 'obligatoire' => true, 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'controle_id' => 1, 'order' => 2, 'nom' => 'Pression pneus', 'description' => null, 'type' => 'NUMERIC', 'obligatoire' => true, 'unit' => 'bar', 'value_min' => 2, 'value_max' => 3, 'created_at' => now(), 'updated_at' => now()],

            // Masque Dräger
            ['id' => 3, 'controle_id' => 4, 'order' => 1, 'nom' => "Contrôle d'étanchéité", 'description' => "Avec l'appareil de contrôle à -10 mbar.", 'type' => 'NUMERIC', 'obligatoire' => true, 'unit' => 'mbar/min', 'value_min' => null, 'value_max' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'controle_id' => 4, 'order' => 2, 'nom' => "Pression d'ouverture de la soupape expiratoire", 'description' => 'Flow 10 l/min.', 'type' => 'NUMERIC', 'obligatoire' => true, 'unit' => 'mbar', 'value_min' => 4.2, 'value_max' => 5.7, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'controle_id' => 4, 'order' => 3, 'nom' => 'Contrôle visuel du masque', 'description' => 'État général.', 'type' => 'BOOLEAN', 'obligatoire' => true, 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],

            // Soupape à la demande Dräger
            ['id' => 6, 'controle_id' => 5, 'order' => 1, 'nom' => 'Test général de fonction', 'description' => "Actionner 2 x le mécanisme de surpression, l'air doit s'échapper librement.", 'type' => 'BOOLEAN', 'obligatoire' => true, 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'controle_id' => 5, 'order' => 2, 'nom' => "Contrôle d'étanchéité vanne de dosage", 'description' => "Contrôle d'augmentation de pression.", 'type' => 'NUMERIC', 'obligatoire' => true, 'unit' => 'mbar/min', 'value_min' => 0, 'value_max' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'controle_id' => 5, 'order' => 3, 'nom' => 'Pression de commutation', 'description' => 'Contrôle fonctionnel.', 'type' => 'BOOLEAN', 'obligatoire' => true, 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'controle_id' => 5, 'order' => 4, 'nom' => 'Surpression', 'description' => 'Pression statique.', 'type' => 'NUMERIC', 'obligatoire' => true, 'unit' => 'mbar', 'value_min' => 1, 'value_max' => 3.9, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'controle_id' => 5, 'order' => 5, 'nom' => "Contrôle d'étanchéité", 'description' => 'Étanchéité à basse pression. Générer une surpression de +/- 7,0 mbar.', 'type' => 'NUMERIC', 'obligatoire' => true, 'unit' => 'mbar/min', 'value_min' => null, 'value_max' => 1, 'created_at' => now(), 'updated_at' => now()],

            // Appareil à air comprimé Dräger
            ['id' => 11, 'controle_id' => 6, 'order' => 1, 'nom' => 'Contrôle batterie(s)', 'description' => 'Indiquer en remarque si la ou les batteries ont été changées.', 'type' => 'BOOLEAN', 'obligatoire' => true, 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'controle_id' => 6, 'order' => 2, 'nom' => 'Pression bouteille gauche', 'description' => 'Bouteille 200 / 300 bar.', 'type' => 'NUMERIC', 'obligatoire' => true, 'unit' => 'bar', 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'controle_id' => 6, 'order' => 3, 'nom' => 'Pression bouteille droite', 'description' => 'Bouteille 200 / 300 bar.', 'type' => 'NUMERIC', 'obligatoire' => false, 'unit' => 'bar', 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'controle_id' => 6, 'order' => 4, 'nom' => "Manomètre de l'appareil", 'description' => null, 'type' => 'NUMERIC', 'obligatoire' => true, 'unit' => 'bar', 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 15, 'controle_id' => 6, 'order' => 5, 'nom' => 'Différence bouteille et manomètre', 'description' => null, 'type' => 'NUMERIC', 'obligatoire' => true, 'unit' => 'bar', 'value_min' => -10, 'value_max' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 16, 'controle_id' => 6, 'order' => 6, 'nom' => 'Soupape à la demande', 'description' => null, 'type' => 'COMPOSANT', 'obligatoire' => true, 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 17, 'controle_id' => 6, 'order' => 7, 'nom' => 'Moyenne pression', 'description' => '½ pression de remplissage.', 'type' => 'NUMERIC', 'obligatoire' => true, 'unit' => 'bar', 'value_min' => 6, 'value_max' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 18, 'controle_id' => 6, 'order' => 8, 'nom' => 'Signal avertisseur', 'description' => null, 'type' => 'NUMERIC', 'obligatoire' => true, 'unit' => 'bar', 'value_min' => 50, 'value_max' => 60, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 19, 'controle_id' => 6, 'order' => 9, 'nom' => "Contrôle d'étanchéité", 'description' => 'Perte de pression maximale.', 'type' => 'NUMERIC', 'obligatoire' => true, 'unit' => 'bar/min', 'value_min' => null, 'value_max' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 20, 'controle_id' => 6, 'order' => 10, 'nom' => 'Cornet acoustique', 'description' => null, 'type' => 'BOOLEAN', 'obligatoire' => true, 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 21, 'controle_id' => 6, 'order' => 11, 'nom' => 'Contrôle visuel appareil et masque', 'description' => 'État général.', 'type' => 'BOOLEAN', 'obligatoire' => true, 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 22, 'controle_id' => 6, 'order' => 12, 'nom' => 'Masque', 'description' => null, 'type' => 'COMPOSANT', 'obligatoire' => false, 'unit' => null, 'value_min' => null, 'value_max' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('controle_tache_materiel_types')->insert([
            ['controle_tache_id' => 16, 'materiel_type_id' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['controle_tache_id' => 22, 'materiel_type_id' => 23, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('controle_materiel_types')->insert([
            ['controle_id' => 1, 'materiel_type_id' => 18, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 1, 'materiel_type_id' => 19, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 1, 'materiel_type_id' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 1, 'materiel_type_id' => 21, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 2, 'materiel_type_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 2, 'materiel_type_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 3, 'materiel_type_id' => 18, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 3, 'materiel_type_id' => 19, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 3, 'materiel_type_id' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 3, 'materiel_type_id' => 21, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 3, 'materiel_type_id' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 4, 'materiel_type_id' => 23, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 5, 'materiel_type_id' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['controle_id' => 6, 'materiel_type_id' => 25, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('controle_execs')->insert([
            ['id' => 1, 'controle_id' => 1, 'article_id' => 1, 'executed_at' => now()->subMonths(7), 'date_echeance' => now()->subMonths(7)->addMonths(6), 'executed_by' => $sapeurId, 'trigger_type' => 'PERIODIQUE', 'remarque_globale' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'controle_id' => 1, 'article_id' => 2, 'executed_at' => now()->subMonths(5)->subDays(20), 'date_echeance' => now()->subMonths(5)->subDays(20)->addMonths(6), 'executed_by' => $sapeurId, 'trigger_type' => 'PERIODIQUE', 'remarque_globale' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'controle_id' => 1, 'article_id' => 3, 'executed_at' => now()->subMonths(2), 'date_echeance' => now()->subMonths(2)->addMonths(6), 'executed_by' => $sapeurId, 'trigger_type' => 'PERIODIQUE', 'remarque_globale' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('controle_exec_taches')->insert([
            // Contrôle technique véhicule, article 1 (Tonne-Pompe) : conforme
            ['controle_exec_id' => 1, 'tache_id' => 1, 'task_order_snapshot' => 1, 'nom_snapshot' => 'Niveau des fluides', 'type_snapshot' => 'BOOLEAN', 'unit_snapshot' => null, 'statut' => 'OK', 'value_measured' => null, 'value_min_snapshot' => null, 'value_max_snapshot' => null, 'value_in_range' => null, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            ['controle_exec_id' => 1, 'tache_id' => 2, 'task_order_snapshot' => 2, 'nom_snapshot' => 'Pression pneus', 'type_snapshot' => 'NUMERIC', 'unit_snapshot' => 'bar', 'statut' => null, 'value_measured' => 2.5, 'value_min_snapshot' => 2, 'value_max_snapshot' => 3, 'value_in_range' => true, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            // Contrôle technique véhicule, article 2 (VPM) : pression insuffisante
            ['controle_exec_id' => 2, 'tache_id' => 1, 'task_order_snapshot' => 1, 'nom_snapshot' => 'Niveau des fluides', 'type_snapshot' => 'BOOLEAN', 'unit_snapshot' => null, 'statut' => 'OK', 'value_measured' => null, 'value_min_snapshot' => null, 'value_max_snapshot' => null, 'value_in_range' => null, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            ['controle_exec_id' => 2, 'tache_id' => 2, 'task_order_snapshot' => 2, 'nom_snapshot' => 'Pression pneus', 'type_snapshot' => 'NUMERIC', 'unit_snapshot' => 'bar', 'statut' => null, 'value_measured' => 1.8, 'value_min_snapshot' => 2, 'value_max_snapshot' => 3, 'value_in_range' => false, 'remarque' => 'Pression insuffisante, à regonfler', 'created_at' => now(), 'updated_at' => now()],
            // Contrôle technique véhicule, article 3 (Iveco) : conforme
            ['controle_exec_id' => 3, 'tache_id' => 1, 'task_order_snapshot' => 1, 'nom_snapshot' => 'Niveau des fluides', 'type_snapshot' => 'BOOLEAN', 'unit_snapshot' => null, 'statut' => 'OK', 'value_measured' => null, 'value_min_snapshot' => null, 'value_max_snapshot' => null, 'value_in_range' => null, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
            ['controle_exec_id' => 3, 'tache_id' => 2, 'task_order_snapshot' => 2, 'nom_snapshot' => 'Pression pneus', 'type_snapshot' => 'NUMERIC', 'unit_snapshot' => 'bar', 'statut' => null, 'value_measured' => 2.4, 'value_min_snapshot' => 2, 'value_max_snapshot' => 3, 'value_in_range' => true, 'remarque' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
