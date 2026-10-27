<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fin des heures supplémentaires d'exercice : elles sont désormais des fiches de travail
 * liées à l'exercice, converties par la commande dbs:migrer-heures-exercice (retirée depuis,
 * disponible dans l'historique au commit « Ajoute la commande de conversion des heures
 * supplémentaires en fiches de travail »).
 *
 * La migration refuse de supprimer les tables tant qu'elles contiennent des lignes :
 * la commande supprime les lignes qu'elle convertit.
 * Le down() recrée la structure vide : les données converties ne reviennent pas.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['heure_exercices', 'heure_exercice_types'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                throw new RuntimeException("Heures supplémentaires non converties ({$table}) : convertissez-les d'abord avec la commande dbs:migrer-heures-exercice (version antérieure de l'API)");
            }
        }

        Schema::dropIfExists('heure_exercices');
        Schema::dropIfExists('heure_exercice_types');
    }

    public function down(): void
    {
        Schema::create('heure_exercice_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->decimal('montant');
            $table->unsignedInteger('type');

            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes');

            $table->unsignedBigInteger('ecriture_categorie_id');
            $table->foreign('ecriture_categorie_id')->references('id')->on('ecriture_categories');

            $table->unsignedBigInteger('type_unite_id');
            $table->foreign('type_unite_id')->references('id')->on('type_unites');
        });

        Schema::create('heure_exercices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->decimal('quantite');
            $table->decimal('montant');
            $table->unsignedInteger('type');

            $table->unsignedBigInteger('exercice_id');
            $table->foreign('exercice_id')->references('id')->on('exercices');

            $table->unsignedBigInteger('sapeur_id');
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes');

            $table->unsignedBigInteger('ecriture_categorie_id');
            $table->foreign('ecriture_categorie_id')->references('id')->on('ecriture_categories');

            $table->unsignedBigInteger('type_unite_id');
            $table->foreign('type_unite_id')->references('id')->on('type_unites');

            $table->unsignedBigInteger('heure_exercice_type_id')->nullable();
            $table->foreign('heure_exercice_type_id')->references('id')->on('heure_exercice_types')->onDelete('set null');
        });
    }
};
