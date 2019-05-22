<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupesSapeursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groupes_sapeurs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('groupe_id')->unsigned()->nullable();
            $table->foreign('groupe_id')->references('id')->on('groupes');

            $table->bigInteger('sapeur_id')->unsigned()->nullable();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groupes_sapeurs');
    }
}
