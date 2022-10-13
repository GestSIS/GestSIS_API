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

            $table->bigInteger('materiel_categorie_id')->unsigned()->nullable();
            $table->foreign('materiel_categorie_id')->references('id')->on('materiel_categories');
        });

        Schema::create('materiel_inventaires', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('taille');
            $table->unsignedInteger('quantite');
            $table->string('remarque');

            $table->bigInteger('materiel_type_id')->unsigned()->nullable();
            $table->foreign('materiel_type_id')->references('id')->on('materiel_types');
        });

        Schema::create('materiel_personnels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('taille');
            $table->string('remarque');

            $table->dateTime('attribution');
            $table->dateTime('retour');

            $table->bigInteger('sapeur_id')->unsigned()->unique();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->string('materiel_type');
            $table->bigInteger('materiel_id')->unsigned()->nullable();

            $table->bigInteger('materiel_type_id')->unsigned()->nullable();
            $table->foreign('materiel_type_id')->references('id')->on('materiel_types');
        });

        Schema::create('materiel_nominals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('uuid')->unique();
            $table->string('numero');

            $table->dateTime('achat');
            $table->string('remarque');
        });

        Schema::create('materiel_indiscernables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedInteger('quantite');
        });

        Schema::create('materiel_event_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('nom');
            $table->string('description');

            // Propripétés
            $table->boolean('validable');
        });

        Schema::create('materiel_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->dateTime('date');
            $table->string('remarque');
            $table->boolean('succes')->default(true);

            $table->bigInteger('materiel_nominal_id')->unsigned()->unique();
            $table->foreign('materiel_nominal_id')->references('id')->on('materiel_nominals');

            $table->bigInteger('materiel_event_id')->unsigned()->unique();
            $table->foreign('materiel_event_id')->references('id')->on('materiel_events');
        });

        Schema::create('materiel_event_pour_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('materiel_type_id')->unsigned()->nullable();
            $table->foreign('materiel_type_id')->references('id')->on('materiel_types');

            $table->bigInteger('materiel_event_type_id')->unsigned()->unique();
            $table->foreign('materiel_event_type_id')->references('id')->on('materiel_event_types');
        });

        Schema::create('materiel_alert_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->dateTime('titre');
            $table->string('description');

            $table->integer('seuil_min');
            $table->boolean('dernier');
        });

        Schema::create('materiel_alert_type_pour_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('materiel_alert_type_id')->unsigned()->unique();
            $table->foreign('materiel_alert_type_id')->references('id')->on('materiel_alert_types');

            $table->bigInteger('materiel_event_type_id')->unsigned()->unique();
            $table->foreign('materiel_event_type_id')->references('id')->on('materiel_event_types');
        });

        Schema::create('materiel_alerts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->dateTime('titre');
            $table->string('description');

            $table->bigInteger('materiel_nominal_id')->unsigned()->unique();
            $table->foreign('materiel_nominal_id')->references('id')->on('materiel_nominals');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('materiel_alert_type_pour_events');
        Schema::dropIfExists('materiel_alert_types');
        Schema::dropIfExists('materiel_event_pour_types');
        Schema::dropIfExists('materiel_events');
        Schema::dropIfExists('materiel_event_types');
        Schema::dropIfExists('materiel_indiscernables');
        Schema::dropIfExists('materiel_alerts');
        Schema::dropIfExists('materiel_nominals');
        Schema::dropIfExists('materiel_personnels');
        Schema::dropIfExists('materiel_inventaires');
        Schema::dropIfExists('materiel_types');
        Schema::dropIfExists('materiel_categories');
    }
}
