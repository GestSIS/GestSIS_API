<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
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

            $table->unsignedBigInteger('precedent_id')->nullable();
            $table->foreign('precedent_id')->references('id')->on('cours');

            $table->unsignedBigInteger('grade_id')->nullable();
            $table->foreign('grade_id')->references('id')->on('grades');

            $table->unsignedBigInteger('fonction_id')->nullable();
            $table->foreign('fonction_id')->references('id')->on('fonctions')->onDelete('set null');

            $table->string('abreviation');
            $table->string('designation');
            $table->integer('tri');
            $table->date('validite_debut')->nullable();
            $table->date('validite_fin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('cours');
        Schema::enableForeignKeyConstraints();
    }
};
