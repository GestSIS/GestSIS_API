<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Contrôles : définition des contrôles à effectuer sur les types de
 * matériel, de leurs tâches, et enregistrement de leurs exécutions par article.
 *
 * Ajoute au passage la péremption, saisie directement sur l'article, et retire
 * le module « maintenance » esquissé par la migration du module matériel : il
 * n'a jamais été exposé (ni route, ni contrôleur, ni écran) et modélisait le
 * même besoin que les contrôles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controles', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->enum('recurrence_type', ['PERIODIQUE', 'NON_PERIODIQUE']);
            $table->unsignedSmallInteger('recurrence_value')->nullable()->comment('Nombre de mois, uniquement pour PERIODIQUE ; null pour NON_PERIODIQUE');
            $table->unsignedSmallInteger('duree_preavis')->nullable()->comment('Nombre de mois avant échéance à partir duquel prévenir, uniquement pour PERIODIQUE');
            $table->unsignedSmallInteger('nb_execution_max')->nullable()->comment('Nombre d\'exécutions au-delà duquel un article est considéré inutilisable, uniquement pour NON_PERIODIQUE');
            $table->unsignedSmallInteger('nb_execution_preavis')->nullable()->comment('Nombre d\'exécutions à partir duquel avertir avant nb_execution_max');
            $table->boolean('externe')->default(false)->comment('Contrôle réalisé par un responsable externe');
            $table->string('reparateur')->nullable()->comment('Responsable du contrôle (interne ou externe)');
            $table->timestamps();
        });

        Schema::create('controle_taches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('controle_id')->constrained('controles')->cascadeOnDelete();
            $table->unsignedSmallInteger('order')->comment('Position dans la séquence d\'exécution');
            $table->string('nom');
            $table->text('description')->nullable();
            $table->enum('type', ['BOOLEAN', 'NUMERIC']);
            $table->string('unit')->nullable()->comment('Unité de mesure pour NUMERIC (bar, Hz, mm…)');
            $table->decimal('value_min', 10, 4)->nullable()->comment('Borne minimale admissible (NUMERIC)');
            $table->decimal('value_max', 10, 4)->nullable()->comment('Borne maximale admissible (NUMERIC)');
            $table->timestamps();
        });

        Schema::create('controle_materiel_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('controle_id')->constrained('controles')->cascadeOnDelete();
            $table->foreignId('materiel_type_id')->constrained('materiel_types')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['controle_id', 'materiel_type_id'], 'controle_materiel_type_unique');
        });

        Schema::create('controle_execs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('controle_id')->constrained('controles')->restrictOnDelete();
            $table->foreignId('article_id')->constrained('articles')->restrictOnDelete();
            $table->dateTime('executed_at');
            $table->foreignId('executed_by')->constrained('sapeurs')->restrictOnDelete();
            $table->enum('trigger_type', ['PERIODIQUE', 'NON_PERIODIQUE']);
            $table->text('remarque_globale')->nullable();
            $table->timestamps();
        });

        Schema::create('controle_exec_taches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('controle_exec_id')->constrained('controle_execs')->cascadeOnDelete();
            $table->foreignId('tache_id')->constrained('controle_taches')->restrictOnDelete();
            $table->unsignedSmallInteger('task_order_snapshot')->comment('Copie de l\'ordre au moment de l\'exécution');
            // BOOLEAN
            $table->enum('statut', ['OK', 'KO', 'NA'])->nullable();
            // NUMERIC
            $table->decimal('value_measured', 10, 4)->nullable();
            $table->decimal('value_min_snapshot', 10, 4)->nullable()->comment('Copie de value_min au moment de l\'exécution');
            $table->decimal('value_max_snapshot', 10, 4)->nullable()->comment('Copie de value_max au moment de l\'exécution');
            $table->boolean('value_in_range')->nullable()->comment('Calculé côté serveur à la soumission');
            // Commun
            $table->text('remarque')->nullable();
            $table->timestamps();
        });

        Schema::table('materiel_types', function (Blueprint $table) {
            $table->boolean('est_perimable')->default(false)->comment('Les articles de ce type portent une date de péremption');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->date('date_peremption')->nullable()->comment('Date de péremption saisie sur l\'article, demandée si le type est périmable');
        });

        Schema::dropIfExists('maintenance_articles');
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('maintenance_type_pour');
        Schema::dropIfExists('maintenance_types');
    }

    public function down(): void
    {
        Schema::create('maintenance_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->integer('periodicite');
            $table->integer('nb_max');
            $table->boolean('externalise');
        });

        Schema::create('maintenance_type_pour', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->foreignId('maintenance_type_id')->constrained();
            $table->foreignId('materiel_type_id')->constrained();

            $table->unique(['maintenance_type_id', 'materiel_type_id'], 'maintenance_type_pour_unique');
        });

        Schema::create('maintenances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->date('date');
            $table->string('remarque')->default('');
            $table->string('responsable');

            $table->foreignId('maintenance_type_id')->constrained();
        });

        Schema::create('maintenance_articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->boolean('effectuee');
            $table->boolean('reussie');
            $table->string('remarque')->default('');

            $table->foreignId('maintenance_id')->constrained();
            $table->foreignId('article_id')->constrained();

            $table->unique(['maintenance_id', 'article_id']);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('date_peremption');
        });

        Schema::table('materiel_types', function (Blueprint $table) {
            $table->dropColumn('est_perimable');
        });

        Schema::dropIfExists('controle_exec_taches');
        Schema::dropIfExists('controle_execs');
        Schema::dropIfExists('controle_materiel_types');
        Schema::dropIfExists('controle_taches');
        Schema::dropIfExists('controles');
    }
};
