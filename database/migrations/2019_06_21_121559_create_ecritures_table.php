<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcrituresTable extends Migration
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

            $table->unsignedBigInteger('type_unite_id');
            $table->foreign('type_unite_id')->references('id')->on('type_unites');

            $table->decimal('quantite');
            $table->decimal('solde_min')->nullable();
            $table->decimal('solde_min_pour')->nullable();
            $table->decimal('taux')->nullable();
            $table->string('taux_description')->nullable();

            $table->decimal('solde');
            $table->decimal('indemnite');
            $table->decimal('frais');

            $table->unsignedBigInteger('sapeur_id');
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes');

            $table->unsignedBigInteger('exercice_comptable_id');
            $table->foreign('exercice_comptable_id')->references('id')->on('exercice_comptables');

            $table->unsignedBigInteger('intervention_id')->nullable();
            $table->foreign('intervention_id')->references('id')->on('interventions');

            $table->unsignedBigInteger('exercice_id')->nullable();
            $table->foreign('exercice_id')->references('id')->on('exercices');

            $table->unsignedBigInteger('indemnite_annuel_type_id')->nullable();
            $table->foreign('indemnite_annuel_type_id')->references('id')->on('indemnite_annuel_types');

            $table->unsignedBigInteger('frais_annuel_type_id')->nullable();
            $table->foreign('frais_annuel_type_id')->references('id')->on('frais_annuel_types');

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
}
