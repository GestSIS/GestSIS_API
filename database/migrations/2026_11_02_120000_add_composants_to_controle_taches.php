<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tâche de type COMPOSANT : son résultat est un article scanné (ex : la
 * pulmocommande et le masque utilisés lors du contrôle d'un cacolet) et non un
 * statut ou une mesure. Seules les tâches COMPOSANT et NUMERIC peuvent être facultatives.
 *
 * Les types d'articles acceptés sont portés par controle_tache_materiel_types.
 * Un même type peut être accepté par plusieurs tâches d'un contrôle, mais un
 * article ne sert qu'une fois dans une exécution.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('controle_taches', function (Blueprint $table) {
            $table->enum('type', ['BOOLEAN', 'NUMERIC', 'COMPOSANT'])->change();
            $table->boolean('obligatoire')->default(true)->after('type')->comment('Seules les tâches COMPOSANT et NUMERIC peuvent être facultatives');
        });

        Schema::create('controle_tache_materiel_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('controle_tache_id')->constrained('controle_taches')->cascadeOnDelete();
            $table->foreignId('materiel_type_id')->constrained('materiel_types')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['controle_tache_id', 'materiel_type_id'], 'controle_tache_materiel_type_unique');
        });

        Schema::table('controle_exec_taches', function (Blueprint $table) {
            $table->enum('type_snapshot', ['BOOLEAN', 'NUMERIC', 'COMPOSANT'])->comment('Copie du type de la tâche au moment de l\'exécution')->change();
            $table->boolean('obligatoire_snapshot')->default(true)->after('type_snapshot')->comment('Copie du caractère obligatoire de la tâche au moment de l\'exécution');
            $table->foreignId('article_id')->nullable()->after('tache_id')->comment('Article scanné pour une tâche COMPOSANT')->constrained('articles')->restrictOnDelete();

            $table->unique(['controle_exec_id', 'article_id'], 'controle_exec_tache_article_unique');
        });
    }

    public function down(): void
    {
        Schema::table('controle_exec_taches', function (Blueprint $table) {
            // MySQL a pu retirer l'index implicite de cette clé au profit de l'index
            // unique : la clé doit être retirée pour pouvoir supprimer celui-ci.
            $table->dropForeign(['controle_exec_id']);
            $table->dropForeign(['article_id']);
            $table->dropUnique('controle_exec_tache_article_unique');
            $table->dropColumn(['article_id', 'obligatoire_snapshot']);
            $table->enum('type_snapshot', ['BOOLEAN', 'NUMERIC'])->comment('Copie du type de la tâche au moment de l\'exécution')->change();
            $table->foreign('controle_exec_id')->references('id')->on('controle_execs')->cascadeOnDelete();
        });

        Schema::dropIfExists('controle_tache_materiel_types');

        Schema::table('controle_taches', function (Blueprint $table) {
            $table->dropColumn('obligatoire');
            $table->enum('type', ['BOOLEAN', 'NUMERIC'])->change();
        });
    }
};
