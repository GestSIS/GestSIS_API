<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterventionSapeurTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intervention_sapeur', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->dateTime('debut');
            $table->dateTime('fin');

            $table->boolean('piquet');

            $table->bigInteger('sapeur_id')->unsigned();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->bigInteger('intervention_id')->unsigned();
            $table->foreign('intervention_id')->references('id')->on('interventions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('intervention_sapeur');
    }
}
