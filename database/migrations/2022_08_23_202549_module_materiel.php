<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuleMateriel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('materiel_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');

            $table->bigInteger('materiel_categorie_id')->unsigned()->nullable();
            $table->foreign('materiel_categorie_id')->references('id')->on('materiel_categories');
        });

        Schema::create('materiel_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->boolean('attribuable');
            $table->boolean('nominal');

            $table->bigInteger('materiel_categorie_id')->unsigned()->nullable();
            $table->foreign('materiel_categorie_id')->references('id')->on('materiel_categories');
        });

        Schema::create('materiel_nominals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('uuid')->unique();

            $table->string('taille');
            $table->dateTime('achat');
            $table->string('remarque');

            $table->bigInteger('materiel_type_id')->unsigned()->unique();
            $table->foreign('materiel_type_id')->references('id')->on('materiel_types');
        });

        Schema::create('materiel_nominal_attributions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->dateTime('attribution');
            $table->dateTime('retour');
            $table->string('remarque');

            $table->bigInteger('sapeur_id')->unsigned()->unique();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->bigInteger('materiel_nominal_id')->unsigned()->unique();
            $table->foreign('materiel_nominal_id')->references('id')->on('materiel_nominals');
        });

        Schema::create('materiel_attributions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedInteger('quantite');
            $table->string('taille');
            $table->string('remarque');
            $table->dateTime('attribution');
            $table->dateTime('retour');

            $table->bigInteger('sapeur_id')->unsigned()->unique();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->bigInteger('materiel_type_id')->unsigned()->unique();
            $table->foreign('materiel_type_id')->references('id')->on('materiel_types');
        });

        Schema::create('materiel_event_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
        });

        Schema::create('materiel_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->dateTime('date');
            $table->dateTime('remarque');

            $table->bigInteger('materiel_nominal_id')->unsigned()->unique();
            $table->foreign('materiel_nominal_id')->references('id')->on('materiel_nominals');

            $table->bigInteger('materiel_event_id')->unsigned()->unique();
            $table->foreign('materiel_event_id')->references('id')->on('materiel_events');
        });

        Schema::create('materiel_event_pour_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('materiel_type_id')->unsigned()->unique();
            $table->foreign('materiel_type_id')->references('id')->on('materiel_types');

            $table->bigInteger('materiel_event_type_id')->unsigned()->unique();
            $table->foreign('materiel_event_type_id')->references('id')->on('materiel_event_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('materiel_event_pour_types');
        Schema::dropIfExists('materiel_events');
        Schema::dropIfExists('materiel_event_types');
        Schema::dropIfExists('materiel_attributions');
        Schema::dropIfExists('materiel_nominals');
        Schema::dropIfExists('materiel_types');
        Schema::dropIfExists('materiel_categories');
    }
}
