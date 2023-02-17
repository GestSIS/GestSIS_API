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
        Schema::create('interventions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            // TODO: Merge ?
            $table->date('date_debut');
            $table->time('heure_debut');
            $table->date('date_fin');
            $table->time('heure_fin');

            $table->string('lieu');
            $table->string('objet');
            $table->boolean('rapport_police');
            $table->smallInteger('degre');
            $table->integer('sauve_personne');
            $table->integer('sauve_animaux');
            $table->text('description');
            $table->text('proprietaire');
            $table->text('responsable');
            $table->integer('stat_nb');

            $table->smallInteger('statut');

            $table->dateTime('date_imputation')->nullable();

            //            $table->decimal('latitude');
            //            $table->decimal('longitude');
            //            $table->string('gps_info');

            $table->bigInteger('exercice_comptable_id')->unsigned();
            $table->foreign('exercice_comptable_id')->references('id')->on('exercice_comptables');

            $table->bigInteger('localite_id')->unsigned();
            $table->foreign('localite_id')->references('id')->on('localites');

            $table->bigInteger('type_intervention_id')->unsigned();
            $table->foreign('type_intervention_id')->references('id')->on('type_interventions');

            $table->bigInteger('sapeur_id')->unsigned();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->bigInteger('stat_federal_id')->unsigned();
            $table->foreign('stat_federal_id')->references('id')->on('stat_federals');

            $table->bigInteger('intervention_traitement_id')->unsigned();
            $table->foreign('intervention_traitement_id')->references('id')->on('intervention_traitements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interventions');
    }
};
