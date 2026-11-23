<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module inventaire (phase 2).
 *
 * Remplace les anciennes tables `inventaires` / `inventaire_articles`, jamais
 * utilisées (aucune route), par :
 * - `inventaires` : un inventaire sur une branche d'emplacements, de son démarrage
 *   à sa validation par le sapeur ;
 * - `inventaire_emplacements` : le périmètre figé au démarrage (copie de l'arbre),
 *   pour relire un inventaire même si les emplacements ont changé depuis ;
 * - `inventaire_lignes` : une ligne par pièce attendue ou trouvée en plus. Un écart
 *   est une ligne absente, défectueuse ou en plus ; son traitement (décision, qui,
 *   quand) est porté par la ligne. Pour un lot, la décision vaut pour toutes les
 *   lignes de l'écart traité : la quantité (manquantes ou en plus) ou les défectueuses.
 *
 * Les règles (un seul inventaire en cours par branche, une ligne par pièce
 * attendue…) sont garanties par le code, pas par des contraintes de la base.
 * Les auteurs sont des sapeurs (`*_par_sapeur_id`), comme pour les attributions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('inventaire_articles');
        Schema::dropIfExists('inventaires');

        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('designation');
            $table->date('date')->comment('Date de l\'inventaire (jour du comptage)');
            $table->enum('statut', ['EN_COURS', 'VALIDE'])->default('EN_COURS');
            $table->text('remarque')->nullable()->comment('Remarque globale du sapeur à la validation');

            // Le périmètre est dans inventaire_emplacements : sa racine est le nœud sans parent.
            $table->foreignId('demarre_par_sapeur_id')->nullable()->constrained('sapeurs')->nullOnDelete();
            $table->foreignId('valide_par_sapeur_id')->nullable()->constrained('sapeurs')->nullOnDelete();
            $table->dateTime('valide_le')->nullable();
        });

        Schema::create('inventaire_emplacements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventaire_id')->constrained('inventaires')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()
                ->comment('Parent dans l\'arbre figé ; null pour la racine du périmètre')
                ->constrained('inventaire_emplacements')->cascadeOnDelete();
            $table->foreignId('emplacement_id')->nullable()
                ->comment('Emplacement d\'origine ; null s\'il a été supprimé depuis')
                ->constrained('emplacements')->nullOnDelete();

            $table->string('designation')->comment('Désignation au démarrage');
            $table->integer('tri')->default(0);
        });

        Schema::create('inventaire_lignes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            
            $table->foreignId('inventaire_emplacement_id')->constrained('inventaire_emplacements')->cascadeOnDelete();
            $table->foreignId('article_id')->nullable()
            ->comment('Null pour une pièce trouvée en plus, pas encore en stock')
            ->constrained('articles')->restrictOnDelete();
            
            $table->enum('origine', ['ATTENDU', 'EN_PLUS'])->comment('ATTENDU = pièce prévue au démarrage ; EN_PLUS = matériel trouvé en plus');
            $table->enum('statut', ['PRESENT', 'ABSENT', 'DEFECTUEUX'])->nullable()->comment('Null = à vérifier');
            
            $table->foreignId('materiel_type_id')->nullable()
            ->comment('Type au démarrage, ou type choisi pour du matériel en plus ; null = type à créer au traitement')
            ->constrained('materiel_types')->nullOnDelete();

            $table->string('numero')->nullable()->comment('Numéro au démarrage (ou saisi pour du matériel en plus) ; null si non numéroté');
            $table->string('designation')->comment('Libellé figé au démarrage, ou nom saisi par le sapeur pour du matériel en plus d\'un type à créer');
            $table->string('compartiment')->default('')->comment('Compartiment au démarrage');
            $table->string('taille')->nullable();
            $table->date('date_peremption')->nullable();
            $table->boolean('est_etiquete')->default(false);
            
            $table->foreignId('verifie_par_sapeur_id')->nullable()->constrained('sapeurs')->nullOnDelete();
            $table->dateTime('verifie_le')->nullable();
            $table->text('remarque_sapeur')->nullable()->comment('Précision ajoutée par le sapeur lors de l\'inventaire');

            $table->foreignId('traite_par_sapeur_id')->nullable()->constrained('sapeurs')->nullOnDelete();
            $table->dateTime('traite_le')->nullable();
            $table->text('remarque_traitement')->nullable();
            $table->enum('decision', ['SORTIR_DU_STOCK', 'AJOUTER_AU_STOCK', 'AUCUNE_ACTION'])->nullable()
            ->comment('Traitement d\'un écart ; null = à traiter (ou pas un écart)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaire_lignes');
        Schema::dropIfExists('inventaire_emplacements');
        Schema::dropIfExists('inventaires');

        Schema::create('inventaires', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->date('date');
            $table->string('designation');
            $table->string('remarque')->default('');
            $table->string('responsable');
            $table->foreignId('emplacement_id')->constrained();
        });

        Schema::create('inventaire_articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->boolean('present');
            $table->foreignId('inventaire_id')->constrained();
            $table->foreignId('article_id')->constrained();
            $table->unique(['article_id', 'inventaire_id']);
        });
    }
};
