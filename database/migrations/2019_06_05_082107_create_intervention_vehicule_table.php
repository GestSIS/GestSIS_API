<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInterventionVehiculeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intervention_vehicule', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->decimal('forfait', 5, 2);
            $table->decimal('utilisation', 5, 2);
            $table->decimal('tarif_unite', 5, 2);

            $table->bigInteger('intervention_id')->unsigned();
            $table->foreign('intervention_id')->references('id')->on('interventions');

            $table->bigInteger('vehicule_id')->unsigned();
            $table->foreign('vehicule_id')->references('id')->on('vehicules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('intervention_vehicule');
    }
}
