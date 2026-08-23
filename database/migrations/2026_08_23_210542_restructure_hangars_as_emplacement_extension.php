<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Un hangar n'est pas un article : c'est un emplacement particulier qui porte
        // des caractéristiques supplémentaires (adresse). La table `hangars` existait
        // déjà mais était totalement déconnectée (pas de lien, jamais exposée) ; on la
        // restructure en extension d'emplacement à clé partagée, comme
        // materiel_type_tuyaux/materiel_type_batteries étendent materiel_types.
        Schema::dropIfExists('hangars');
        Schema::create('hangars', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->foreign('id')->references('id')->on('emplacements')->onDelete('cascade');
            $table->timestamps();

            $table->string('rue')->default('');
            $table->string('no_rue')->default('');
            $table->foreignId('localite_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hangars');
        Schema::create('hangars', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('designation');
            $table->string('rue')->default('');
            $table->string('no_rue')->default('');
            $table->boolean('statut')->default(true);

            $table->foreignId('localite_id')->constrained();
        });
    }
};
