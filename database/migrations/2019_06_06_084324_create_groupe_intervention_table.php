<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupeInterventionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groupe_intervention', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('groupe_id')->unsigned();
            $table->foreign('groupe_id')->references('id')->on('groupes');

            $table->bigInteger('intervention_id')->unsigned();
            $table->foreign('intervention_id')->references('id')->on('interventions');


            $table->unique(['groupe_id', 'intervention_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groupe_intervention');
    }
}
