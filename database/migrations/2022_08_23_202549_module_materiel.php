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

            $table->unsignedBigInteger('pere_id')->nullable()->default(null);
            $table->foreign('pere_id')->references('id')->on('materiel_categories');
        });

        Schema::create('materiel_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->boolean('taille')->default(true);

            $table->unsignedBigInteger('materiel_categorie_id')->nullable();
            $table->foreign('materiel_categorie_id')->references('id')->on('materiel_categories');
        });

        Schema::create('materiel_personnels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('taille')->default('');
            $table->string('remarque')->default('');

            $table->date('attribution')->nullable()->default(null);
            $table->date('retour')->nullable()->default(null);

            $table->unsignedBigInteger('sapeur_id')->nullable()->default(null);
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->unsignedBigInteger('materiel_type_id');
            $table->foreign('materiel_type_id')->references('id')->on('materiel_types');

            // Relation polymorphique
            $table->string('materiel_type');
            $table->unsignedBigInteger('materiel_id')->nullable();
        });

        Schema::create('materiel_nominals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('uuid')->unique();
            $table->string('numero');

            $table->string('achat');
        });

        Schema::create('materiel_generiques', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedInteger('quantite');
        });

        Schema::create('materiel_event_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('nom');
            $table->string('description')->default('');

            // Propriétés
            $table->boolean('validable');
        });

        Schema::create('materiel_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->dateTime('date');
            $table->string('remarque')->default('');
            $table->boolean('succes')->default(true);

            $table->unsignedBigInteger('materiel_nominal_id');
            $table->foreign('materiel_nominal_id')->references('id')->on('materiel_nominals')->onDelete('cascade');

            $table->unsignedBigInteger('materiel_event_id');
            $table->foreign('materiel_event_id')->references('id')->on('materiel_events');
        });

        Schema::create('materiel_event_type_pour', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedBigInteger('materiel_type_id');
            $table->foreign('materiel_type_id')->references('id')->on('materiel_types')->onDelete('cascade');

            $table->unsignedBigInteger('materiel_event_type_id');
            $table->foreign('materiel_event_type_id')->references('id')->on('materiel_event_types')->onDelete('cascade');
        });

        Schema::create('materiel_alerte_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('titre');
            $table->string('description')->default('');

            $table->integer('seuil_min');
            $table->boolean('dernier');
        });

        Schema::create('materiel_alerte_type_pour', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedBigInteger('materiel_alerte_type_id');
            $table->foreign('materiel_alerte_type_id')->references('id')->on('materiel_alerte_types')->onDelete('cascade');

            $table->unsignedBigInteger('materiel_event_type_id');
            $table->foreign('materiel_event_type_id')->references('id')->on('materiel_event_types')->onDelete('cascade');
        });

        Schema::create('materiel_alertes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('titre');
            $table->string('description')->default('');

            $table->string('remarque')->default('');
            $table->integer('statut');

            $table->unsignedBigInteger('materiel_nominal_id');
            $table->foreign('materiel_nominal_id')->references('id')->on('materiel_nominals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('materiel_alerte_type_pour');
        Schema::dropIfExists('materiel_alerte_types');
        Schema::dropIfExists('materiel_event_type_pour');
        Schema::dropIfExists('materiel_events');
        Schema::dropIfExists('materiel_event_types');
        Schema::dropIfExists('materiel_generiques');
        Schema::dropIfExists('materiel_alertes');
        Schema::dropIfExists('materiel_nominals');
        Schema::dropIfExists('materiel_personnels');
        Schema::dropIfExists('materiel_types');
        Schema::dropIfExists('materiel_categories');
    }
};
