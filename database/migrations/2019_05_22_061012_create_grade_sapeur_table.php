<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGradeSapeurTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grade_sapeur', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('grade_id')->unsigned()->nullable();
            $table->foreign('grade_id')->references('id')->on('grades');

            $table->bigInteger('sapeur_id')->unsigned()->nullable();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->date('date');
            $table->string('remarque');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grades_sapeurs');
    }
}
