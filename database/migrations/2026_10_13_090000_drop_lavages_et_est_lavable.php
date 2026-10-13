<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fin du suivi des lavages à part : ils sont désormais des exécutions du
 * contrôle « Lavage », reprises par la commande dbs:migrate-lavages.
 *
 * Le down() recrée la structure vide : les lavages supprimés ne reviennent
 * pas (ils subsistent comme exécutions du contrôle « Lavage »).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::drop('lavages');
        Schema::table('materiel_types', function (Blueprint $table) {
            $table->dropColumn('est_lavable');
        });
    }

    public function down(): void
    {
        Schema::create('lavages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->foreignId('article_id')->constrained();
            $table->date('date');
        });

        Schema::table('materiel_types', function (Blueprint $table) {
            $table->boolean('est_lavable')->default(false)->comment('Pour activer le suivi des lavages');
        });
    }
};
