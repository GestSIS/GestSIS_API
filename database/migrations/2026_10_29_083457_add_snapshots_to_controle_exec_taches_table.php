<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Une exécution enregistrée ne dépend plus de la configuration actuelle des
 * tâches de son contrôle : en plus de l'ordre et des bornes déjà copiés, le
 * nom, le type et l'unité de la tâche sont figés sur chaque ligne de
 * résultat. Renommer une tâche, changer son type ou la supprimer ne modifie
 * donc plus l'historique.
 *
 * `tache_id` devient nullable (nullOnDelete) : une tâche ayant des résultats
 * peut être supprimée, ses résultats passés restant lisibles grâce aux
 * champs figés.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('controle_exec_taches', function (Blueprint $table) {
            $table->string('nom_snapshot')->nullable()->after('task_order_snapshot')->comment('Copie du nom de la tâche au moment de l\'exécution');
            $table->enum('type_snapshot', ['BOOLEAN', 'NUMERIC'])->nullable()->after('nom_snapshot')->comment('Copie du type de la tâche au moment de l\'exécution');
            $table->string('unit_snapshot')->nullable()->after('type_snapshot')->comment('Copie de l\'unité de la tâche (NUMERIC) au moment de l\'exécution');
        });

        DB::statement(
            'UPDATE controle_exec_taches cet
             JOIN controle_taches ct ON ct.id = cet.tache_id
             SET cet.nom_snapshot = ct.nom, cet.type_snapshot = ct.type, cet.unit_snapshot = ct.unit'
        );

        Schema::table('controle_exec_taches', function (Blueprint $table) {
            $table->string('nom_snapshot')->nullable(false)->change();
            $table->enum('type_snapshot', ['BOOLEAN', 'NUMERIC'])->nullable(false)->change();

            $table->dropForeign(['tache_id']);
            $table->unsignedBigInteger('tache_id')->nullable()->change();
            $table->foreign('tache_id')->references('id')->on('controle_taches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('controle_exec_taches', function (Blueprint $table) {
            $table->dropForeign(['tache_id']);
            $table->unsignedBigInteger('tache_id')->nullable(false)->change();
            $table->foreign('tache_id')->references('id')->on('controle_taches')->restrictOnDelete();

            $table->dropColumn(['nom_snapshot', 'type_snapshot', 'unit_snapshot']);
        });
    }
};
