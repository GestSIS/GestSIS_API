<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExerciceComptablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercice_comptables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->integer('annee');
            $table->string('designation');
            $table->date('debut');
            $table->date('fin');
            $table->integer('boucle');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exercice_comptables');
    }
}
