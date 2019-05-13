<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cours', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            
            $table->unsignedBigInteger('precedent')->nullable();
            $table->foreign('precedent')->references('id')->on('cours');

            $table->unsignedBigInteger('grade')->nullable();
            $table->foreign('grade')->references('id')->on('grades');

            $table->string('abreviation');
            $table->string('designation');
            $table->integer('tri');
            $table->smallInteger('status');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cours');
    }
}
