<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJustificatifsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('justificatifs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('filename');
            $table->string('logicalname');

            $table->unsignedBigInteger('controle_medical_id');
            $table->foreign('controle_medical_id')->references('id')->on('controles_medicaux');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('justificatifs');
    }
}
