<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExercicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('localite_id')->unsigned()->nullable();
            $table->foreign('localite_id')->references('id')->on('localites');

            $table->bigInteger('exercice_categorie_id')->unsigned();
            $table->foreign('exercice_categorie_id')->references('id')->on('exercice_categories');

            $table->bigInteger('exercice_comptable_id')->unsigned();
            $table->foreign('exercice_comptable_id')->references('id')->on('exercice_comptables');

            $table->date('date');
            $table->time('heure');
            $table->string('lieu');
            $table->string('communication');
            $table->string('designation');
            $table->integer('duree');
            $table->integer('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exercices');
    }
}
