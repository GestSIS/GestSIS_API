<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuleTravail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travail_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->boolean('actif');

            $table->unsignedBigInteger('type_unite_id');
            $table->foreign('type_unite_id')->references('id')->on('type_unites');

            $table->unsignedBigInteger('ecriture_categorie_id');
            $table->foreign('ecriture_categorie_id')->references('id')->on('ecriture_categories');
        });

        Schema::create('travail_type_fonctions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->decimal('tarif');
            $table->unsignedInteger('type');

            $table->unsignedBigInteger('fonction_id')->nullable()->default(null);
            $table->foreign('fonction_id')->references('id')->on('fonctions');

            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes');

            $table->unsignedBigInteger('travail_type_id');
            $table->foreign('travail_type_id')->references('id')->on('travail_types');
        });

        Schema::create('travaux', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->date('date');
            $table->string('designation');
            $table->decimal('quantite');

            $table->date('date_demande');
            $table->string('justification');
            $table->integer('statut');

            $table->unsignedBigInteger('exercice_comptable_id');
            $table->foreign('exercice_comptable_id')->references('id')->on('exercice_comptables');

            $table->unsignedBigInteger('sapeur_id');
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->unsignedBigInteger('auteur_id');
            $table->foreign('auteur_id')->references('id')->on('sapeurs');

            $table->unsignedBigInteger('travail_type_id');
            $table->foreign('travail_type_id')->references('id')->on('travail_types');
        });

        Schema::table('ecritures', function (Blueprint $table) {
            $table->unsignedBigInteger('travail_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('travaux');
        Schema::dropIfExists('travail_type_fonctions');
        Schema::dropIfExists('travail_types');

        Schema::table('ecritures', function (Blueprint $table) {
            $table->dropColumn('travail_id');
        });
    }
};
