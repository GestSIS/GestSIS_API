<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controles', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->enum('recurrence_type', ['PERIODIQUE', 'NON_PERIODIQUE', 'APRES_USAGE']);
            $table->unsignedSmallInteger('recurrence_value')->nullable()->comment('Nombre de mois, uniquement pour PERIODIQUE ; null pour NON_PERIODIQUE et APRES_USAGE');
            $table->unsignedSmallInteger('duree_preavis')->nullable()->comment('Nombre de mois avant échéance à partir duquel prévenir, uniquement pour PERIODIQUE');
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
        });

        Schema::create('controle_execs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('controle_id')->constrained('controles')->restrictOnDelete();
            $table->foreignId('article_id')->constrained('articles')->restrictOnDelete();
            $table->dateTime('executed_at');
            $table->foreignId('executed_by')->constrained('sapeurs')->restrictOnDelete();
            $table->enum('trigger_type', ['PERIODIQUE', 'NON_PERIODIQUE', 'APRES_USAGE']);
            $table->text('remarque_globale')->nullable();
            $table->timestamps();
        });

        Schema::create('controle_exec_taches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('controle_exec_id')->constrained('controle_execs')->cascadeOnDelete();
            $table->foreignId('tache_id')->constrained('controle_taches')->restrictOnDelete();
            $table->unsignedSmallInteger('task_order_snapshot')->comment('Copie de l\'ordre au moment de l\'exécution');
            // BOOLEAN
            $table->enum('statut', ['OK', 'NOK', 'NA'])->nullable();
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
            $table->boolean('est_perimable')->default(false);
            $table->unsignedSmallInteger('duree_peremption')->nullable()->comment('Durée de péremption en mois, utilisé si est_perimable = true');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->date('date_fabrication')->nullable()->comment('Année/mois de fabrication (jour fixé au 1er), utilisé pour calculer la péremption');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('date_fabrication');
        });

        Schema::table('materiel_types', function (Blueprint $table) {
            $table->dropColumn(['est_perimable', 'duree_peremption']);
        });

        Schema::dropIfExists('controle_exec_taches');
        Schema::dropIfExists('controle_execs');
        Schema::dropIfExists('controle_materiel_types');
        Schema::dropIfExists('controle_taches');
        Schema::dropIfExists('controles');
    }
};
