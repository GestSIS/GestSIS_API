<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInterventionMaterielTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intervention_materiel', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->decimal('quantite');

            $table->bigInteger('materiel_id')->unsigned();
            $table->foreign('materiel_id')->references('id')->on('materiels');

            $table->bigInteger('intervention_id')->unsigned();
            $table->foreign('intervention_id')->references('id')->on('interventions');

            $table->unique(['materiel_id', 'intervention_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('intervention_materiel');
    }
}
