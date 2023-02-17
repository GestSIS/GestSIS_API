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
        Schema::create('quittances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('sapeur_id')->unsigned();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->bigInteger('intervention_id')->unsigned();
            $table->foreign('intervention_id')->references('id')->on('interventions');

            $table->unique(['sapeur_id', 'intervention_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quittances');
    }
};
