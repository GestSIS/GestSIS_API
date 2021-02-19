<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExerciceSapeurTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercice_sapeur', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('sapeur_id')->unsigned();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->bigInteger('exercice_id')->unsigned();
            $table->foreign('exercice_id')->references('id')->on('exercices');

            $table->bigInteger('excuse_type_id')->unsigned()->nullable();
            $table->foreign('excuse_type_id')->references('id')->on('excuse_types');

            // TODO: Change into boolean ?
            $table->integer('convoque');
            $table->integer('present');
            $table->integer('amende');
            $table->integer('remplace');
            // $table->numeric('heure', 4, 2)->default(0.0);

            $table->unique(['exercice_id', 'sapeur_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exercice_sapeur');
    }
}
