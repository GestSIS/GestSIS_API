<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecritures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->decimal('total');

            $table->decimal('tarif');
            $table->date('date')->nullable();
            $table->time('heure')->nullable();

            $table->unsignedBigInteger('type_unite_id')->nullable();
            $table->foreign('type_unite_id')->references('id')->on('type_unites');

            $table->decimal('quantite');
            $table->decimal('solde_min')->nullable(); // Renommé
            $table->decimal('solde_min_pour')->nullable(); // Renommé
            $table->decimal('taux')->nullable();
            $table->string('taux_description')->nullable();

            $table->decimal('solde'); // Supprimé
            $table->decimal('indemnite'); // Supprimé
            $table->decimal('frais'); // Supprimé

            $table->boolean('avs')->default(false); // Supprimé
            $table->boolean('amende')->default(false); // Supprimé
            $table->boolean('frais_annuel')->default(false); // Supprimé
            $table->boolean('indemnite_annuel')->default(false); // Supprimé

            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes');

            $table->unsignedBigInteger('exercice_comptable_id');
            $table->foreign('exercice_comptable_id')->references('id')->on('exercice_comptables');

            $table->unsignedBigInteger('ecriture_categorie_id');
            $table->foreign('ecriture_categorie_id')->references('id')->on('ecriture_categories');

            // Utilisé pour les décomptes sapeurs
            $table->unsignedBigInteger('sapeur_id')->nullable();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->unsignedBigInteger('intervention_id')->nullable();
            $table->foreign('intervention_id')->references('id')->on('interventions');

            $table->unsignedBigInteger('exercice_id')->nullable();
            $table->foreign('exercice_id')->references('id')->on('exercices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ecritures');
    }
};
