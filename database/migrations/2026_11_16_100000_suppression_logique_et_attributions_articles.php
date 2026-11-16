<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Suppression logique des articles et historique des attributions (phase 1).
 *
 * - `articles.statut` est remplacé par `supprime_le` (+ auteur et remarque) ;
 *   pour un véhicule, il devient `disponible_intervention`.
 * - `articles.attribution` / `retour` sont repris dans `article_attributions`.
 * - `materiel_types.statut` et `couleurs.statut` permettent de désactiver un
 *   type ou une couleur qui n'est plus utilisé.
 *
 * Le down() recrée les anciennes colonnes et les remplit au mieux : les
 * attributions closes et les auteurs sont perdus.
 */
return new class extends Migration
{
    /**
     * Valeur de MaterielTypeBusiness::TYPE_VEHICULE au moment de la migration,
     * figée ici pour que la migration ne dépende pas du code applicatif.
     */
    private const TYPE_VEHICULE = 3;

    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->date('supprime_le')->nullable();
            $table->foreignId('supprime_par_sapeur_id')->nullable()->constrained('sapeurs')->nullOnDelete();
            $table->text('remarque_suppression')->nullable();
            $table->foreignId('ajoute_par_sapeur_id')->nullable()->constrained('sapeurs')->nullOnDelete();
            $table->boolean('disponible_intervention')->default(true);
        });

        Schema::create('article_attributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->restrictOnDelete();
            $table->foreignId('sapeur_id')->constrained()->restrictOnDelete();
            $table->date('debut');
            $table->date('fin')->nullable();
            $table->foreignId('emplacement_retour_id')->nullable()->constrained('emplacements')->nullOnDelete();
            $table->foreignId('attribue_par_sapeur_id')->nullable()->constrained('sapeurs')->nullOnDelete();
            $table->foreignId('cloture_par_sapeur_id')->nullable()->constrained('sapeurs')->nullOnDelete();
            // Vaut article_id tant que l'attribution est ouverte : l'index unique
            // garantit au plus une attribution ouverte par article.
            $table->unsignedBigInteger('attribution_ouverte')->nullable()->storedAs('IF(fin IS NULL, article_id, NULL)')->unique();
            $table->timestamps();
        });

        Schema::table('materiel_types', function (Blueprint $table) {
            $table->boolean('statut')->default(true);
        });

        Schema::table('couleurs', function (Blueprint $table) {
            $table->boolean('statut')->default(true);
        });

        // Articles inactifs (hors véhicules) : supprimés à la date de la migration.
        DB::statement(
            'UPDATE articles a JOIN materiel_types t ON t.id = a.materiel_type_id
             SET a.supprime_le = CURDATE()
             WHERE a.statut = 0 AND t.type <> ?',
            [self::TYPE_VEHICULE]
        );

        // Véhicules inactifs : restent au stock, mais ne sont plus disponibles en intervention.
        DB::statement(
            'UPDATE articles a JOIN materiel_types t ON t.id = a.materiel_type_id
             SET a.disponible_intervention = 0
             WHERE a.statut = 0 AND t.type = ?',
            [self::TYPE_VEHICULE]
        );

        // Attributions en cours : une attribution ouverte par article attribué, sans auteur.
        DB::statement(
            'INSERT INTO article_attributions (article_id, sapeur_id, debut, fin, created_at, updated_at)
             SELECT id, sapeur_id, COALESCE(attribution, CURDATE()), NULL, NOW(), NOW()
             FROM articles
             WHERE sapeur_id IS NOT NULL'
        );

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['statut', 'attribution', 'retour']);
            $table->dropForeign(['emplacement_id']);
            $table->foreign('emplacement_id')->references('id')->on('emplacements')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['emplacement_id']);
            $table->foreign('emplacement_id')->references('id')->on('emplacements');
            $table->date('attribution')->nullable()->default(null);
            $table->date('retour')->nullable()->default(null);
            $table->boolean('statut')->default(true);
        });

        DB::statement('UPDATE articles SET statut = (supprime_le IS NULL AND disponible_intervention = 1)');

        DB::statement(
            'UPDATE articles a JOIN article_attributions aa ON aa.article_id = a.id AND aa.fin IS NULL
             SET a.attribution = aa.debut'
        );

        Schema::drop('article_attributions');

        Schema::table('couleurs', function (Blueprint $table) {
            $table->dropColumn('statut');
        });

        Schema::table('materiel_types', function (Blueprint $table) {
            $table->dropColumn('statut');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['supprime_par_sapeur_id']);
            $table->dropForeign(['ajoute_par_sapeur_id']);
            $table->dropColumn([
                'supprime_le',
                'supprime_par_sapeur_id',
                'remarque_suppression',
                'ajoute_par_sapeur_id',
                'disponible_intervention',
            ]);
        });
    }
};
