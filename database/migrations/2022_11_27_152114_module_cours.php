<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuleCours extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indemnite_cours_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');

            $table->unsignedBigInteger('ecriture_categorie_id');
            $table->foreign('ecriture_categorie_id')->references('id')->on('ecriture_categories');
        });

        Schema::create('indemnite_cours_fonctions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->decimal('tarif');
            $table->unsignedInteger('type');

            $table->unsignedBigInteger('type_unite_id');
            $table->foreign('type_unite_id')->references('id')->on('type_unites');

            $table->unsignedBigInteger('fonction_id')->nullable();
            $table->foreign('fonction_id')->references('id')->on('fonctions')->onDelete('cascade');

            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes')->onDelete('cascade');

            $table->unsignedBigInteger('indemnite_cours_id');
            $table->foreign('indemnite_cours_id')->references('id')->on('indemnite_cours_types');
        });

        Schema::table('ecritures', function (Blueprint $table) {
            $table->unsignedBigInteger('cours_sapeur_id')->nullable();
            $table->foreign('cours_sapeur_id')->references('id')->on('cours_sapeur');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('indemnite_cours_fonctions');
        Schema::dropIfExists('indemnite_cours_types');

        Schema::table('ecritures', function (Blueprint $table) {
            $table->dropForeign(['cours_sapeur_id']);
            $table->dropColumn('cours_sapeur_id');
        });
    }
};
