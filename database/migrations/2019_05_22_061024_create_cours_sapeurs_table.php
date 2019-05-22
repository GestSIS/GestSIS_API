<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoursSapeursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cours_sapeurs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('cours_id')->unsigned()->nullable();
            $table->foreign('cours_id')->references('id')->on('cours');

            $table->bigInteger('sapeur_id')->unsigned()->nullable();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->date('date');
            $table->string('lieu');//TODO: Check if can be changed to localite_id
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cours_sapeurs');
    }
}
